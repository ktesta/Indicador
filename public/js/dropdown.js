/* Widget close */
$(document).ready(function(){
    $('.wclose').click(function(e){
      e.preventDefault();
      var $wbox = $(this).parent().parent().parent();
      $wbox.hide(100);
    });

    /* Widget minimize */

    $('.wminimize').click(function(e){
        e.preventDefault();
        var $wcontent = $(this).parent().parent().next('.widget-content');
        if($wcontent.is(':visible')) 
        {
          $(this).children('i').removeClass('fa fa-chevron-up');
          $(this).children('i').addClass('fa fa-chevron-down');
        }
        else 
        {
          $(this).children('i').removeClass('fa fa-chevron-down');
          $(this).children('i').addClass('fa fa-chevron-up');
        }            
        $wcontent.toggle(500);
    });
}); 

