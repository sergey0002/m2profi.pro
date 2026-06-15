$( document ).ready(function() {
	$(document).on( 'click', '.fw_panel_label', function() {
	  var panel = $(this).closest(".fw_frontadmin_panel");
	   var panelc= $('.fw_panel_content',panel);
	   
	   
	  // panelc.toggle
		if( panelc.hasClass("fw_panel_disp") ) 
		{
			panelc.removeClass('fw_panel_disp');
			 panelc.addClass('fw_panel_ndisp');
		}
		else
		{
			panelc.removeClass('fw_panel_ndisp');
			panelc.addClass('fw_panel_disp');
		}	
	});
});
