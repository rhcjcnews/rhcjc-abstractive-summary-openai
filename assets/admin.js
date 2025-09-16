jQuery(function($){
  $('#rhcjc_as_regen_btn').on('click', function(){
    const postId = $(this).data('post');
    $(this).prop('disabled', true).text('Regenerating…');
    $.post(RHCJC_AS.ajax, { action: 'rhcjc_as_regen', nonce: RHCJC_AS.nonce, post_id: postId })
      .done(function(resp){
        if (resp && resp.success) {
          alert('Summary regenerated. Click Update, then refresh.');
        } else {
          alert('Error: ' + (resp && resp.data ? resp.data : 'unknown'));
        }
      })
      .fail(function(){ alert('Network error.'); })
      .always(()=> $('#rhcjc_as_regen_btn').prop('disabled', false).text('Regenerate'));
  });
});
