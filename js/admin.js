jQuery(function($){

  var $index_btn = $('#start-indexing');
  var $loading_gif = $('#loading-gif');
  var $window = $(window);
  var wpls_index_running = false;
  $loading_gif.hide();

  $index_btn.click(function(evt){
    evt.preventDefault();
    $(this).attr('disabled', 'disabled');
    $loading_gif.show();
    wpls_index_running = true;
    wpls_search_run_indexer(0, 0, 0);
  });

  $window.bind('beforeunload', function(){
    if( wpls_index_running ){
      return 'Indexing not complete. Are you sure you want to leave this page?';
    }
  });

  function wpls_search_run_indexer( _start, _count, _last_indexed ){
    $.post(ajaxurl, {
      action: 'lucene_search_index',
      start_id: _start,
      count: _count,
      last_indexed: _last_indexed
    },
    function(response){
      if( response.count >= response.total_posts ){
        wpls_index_running = false;
        window.location.href = window.location.href + '&indexed=1';
      } else if(response.total_posts) {
        $('#progress-documents').html('Indexing '+response.count+' of '+response.total_posts);
        wpls_search_run_indexer(response.last_indexed, response.count, response.last_indexed);
      } else {
        // no response
        $index_btn.removeAttr('disabled');
        $loading_gif.hide();
      }
    },
    'json');
  }

});

function wpls_search_console_log(_o){
  if( window.console ){
    console.log(_o);
  }
}