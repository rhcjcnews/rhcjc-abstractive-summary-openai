<?php
if (!defined('ABSPATH')) exit;

/**
 * Calls OpenAI Chat Completions with JSON mode and strict, grounded prompt.
 * Returns array of bullets (validated), and evidence arrays (parallel).
 */
function rhcjc_as_call_openai($article_text, $target_bullets = RHCJC_AS_BULLET_COUNT) {
  $api_key = rhcjc_as_get_api_key();
  if (!$api_key) return new WP_Error('no_api_key', 'OpenAI API key not configured.');

  $model = get_option(RHCJC_AS_OPTION_MODEL, RHCJC_AS_DEFAULT_MODEL);
  if (!$model) $model = RHCJC_AS_DEFAULT_MODEL;

  $endpoint = 'https://api.openai.com/v1/chat/completions';

  // System + user prompts (grounded, JSON, temp=0)
  $system = <<<SYS
You are a neutral news copy editor. Create a concise, factual summary of a single news article as bullet points.
Rules:
- Use ONLY facts present in the article text. Do not invent or add background not present.
- No speculation, no new numbers, no external context.
- Neutral tone. Avoid adjectives/adverbs. Prefer dates, names, numbers.
- Return 4–5 bullets. Each bullet must include 1–3 short evidence spans (verbatim quotes) copied from the article that support the bullet.
- Output STRICT JSON only, in this schema:
{
  "bullets": [
    {"text": "<bullet sentence>", "evidence": ["<short quote>", "..."]},
    ...
  ]
}
If the article is too short to summarize, return {"bullets":[]}.
SYS;

  $user = "ARTICLE (verbatim; your ONLY source of truth):\n\"\"\"\n{$article_text}\n\"\"\"\nReturn ONLY the JSON.";

  $body = [
    'model' => $model,
    'temperature' => RHCJC_AS_TEMPERATURE,
    'max_tokens'  => RHCJC_AS_MAX_TOKENS,
    'response_format' => ['type' => 'json_object'],
    'messages' => [
      ['role'=>'system', 'content'=>$system],
      ['role'=>'user',   'content'=>$user],
    ],
  ];

  $args = [
    'headers' => [
      'Authorization' => 'Bearer '.$api_key,
      'Content-Type'  => 'application/json',
      'Accept'        => 'application/json',
    ],
    'body'    => wp_json_encode($body),
    'timeout' => RHCJC_AS_HTTP_TIMEOUT,
  ];

  rhcjc_as_log('openai_request', ['model'=>$model, 'temp'=>RHCJC_AS_TEMPERATURE, 'len'=>mb_strlen($article_text,'UTF-8')]);

  $res = wp_remote_post($endpoint, $args);
  if (is_wp_error($res)) return $res;

  $code = wp_remote_retrieve_response_code($res);
  $raw  = wp_remote_retrieve_body($res);
  if ($code < 200 || $code >= 300) {
    rhcjc_as_log('openai_http_error', ['code'=>$code,'body'=>$raw]);
    return new WP_Error('openai_http', "OpenAI HTTP $code");
  }

  $json = json_decode($raw, true);
  $content = $json['choices'][0]['message']['content'] ?? '';
  if (!$content) {
    rhcjc_as_log('openai_empty_content', $raw);
    return new WP_Error('openai_empty', 'Empty content from OpenAI.');
  }

  // Parse JSON payload from model
  $parsed = json_decode($content, true);
  if (!is_array($parsed)) {
    // try to salvage if model wrapped extra text (unlikely with JSON mode)
    $start = strpos($content, '{'); $end = strrpos($content, '}');
    if ($start !== false && $end !== false) {
      $parsed = json_decode(substr($content, $start, $end-$start+1), true);
    }
  }
  if (!is_array($parsed)) {
    rhcjc_as_log('json_parse_fail', $content);
    return new WP_Error('json_fail', 'Could not parse JSON from model.');
  }

  $bullets = [];
  $evidences = [];
  $article_lower = mb_strtolower($article_text,'UTF-8');

  foreach ((array)($parsed['bullets'] ?? []) as $item) {
    if (!isset($item['text'])) continue;
    $text = trim((string)$item['text']);
    if ($text === '') continue;

    // Evidence check: require ≥1 evidence span that actually appears
    $ev_ok = false;
    $ev_list = [];
    foreach ((array)($item['evidence'] ?? []) as $quote) {
      $q = trim((string)$quote);
      if ($q === '') continue;
      $ev_list[] = $q;
      if (!$ev_ok) {
        // loose containment: lowercased, ignoring smart quotes
        $q_norm = mb_strtolower(str_replace(['“','”','’'], ['"','"','\''], $q), 'UTF-8');
        $art_norm = str_replace(['“','”','’'], ['"','"','\''], $article_lower);
        if (mb_strpos($art_norm, $q_norm) !== false) $ev_ok = true;
      }
    }

    if ($ev_ok) {
      $bullets[]   = $text;
      $evidences[] = $ev_list;
    }
    if (count($bullets) >= $target_bullets) break;
    if (count($bullets) >= RHCJC_AS_MAX_BULLETS) break;
  }

  if (empty($bullets)) {
    return new WP_Error('no_grounded_bullets', 'Model returned no verifiable bullets.');
  }

  return [
    'bullets'  => rhcjc_as_sanitize_bullets($bullets),
    'evidence' => $evidences,
    'model'    => $model,
    'provider' => 'openai',
  ];
}
