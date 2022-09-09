jQuery(document).ready(function($){
	
	$('.user_roles_not_allowed').select2();
	$('#recurring_data_time').datetimepicker({
	    minDate: 0,  // disable past date
	});
	
	/* 
	**   Generate Password JS
	*	
	*/
	$('#generate_password_btn').click(function(e){
		$(this).hide();
		$('.update_user_password').val('');
		$('#show_password').val("Show");
		var password = generate_password(12,true);
		$('.update_password_container').fadeIn();
		$('#update_user_password').val(password);
	});
	/* 
	**   Show Password JS
	*	
	*/
	$("#show_password").click(function(){

		if($('#update_user_password').prop("type") === "password"){
			$('#update_user_password').prop("type", "text");
			$('#show_password').val("Hide");
		} else if($('#update_user_password').prop("type") === "text"){
			$('#update_user_password').prop("type", "password");
			$('#show_password').val("Show");
		}
		
	});
	
	/* 
	**   Show Password JS
	*	
	*/
	$("#cancel_password").click(function(){
		$('#update_user_password').val("");	
		$('.update_password_container').hide();	
		$('#generate_password_btn').show();
	});
	
	/* 
	**   generate_password function
	*	
	*/	
	function generate_password(length, special) {
	    var iteration = 0;
	    var password = "";
	    var randomNumber;
	    if(special == undefined){
	        var special = false;
	    }
	    while(iteration < length){
	        randomNumber = (Math.floor((Math.random() * 100)) % 94) + 33;
	        if(!special){
	            if ((randomNumber >=33) && (randomNumber <=47)) { continue; }
	            if ((randomNumber >=58) && (randomNumber <=64)) { continue; }
	            if ((randomNumber >=91) && (randomNumber <=96)) { continue; }
	            if ((randomNumber >=123) && (randomNumber <=126)) { continue; }
	        }
	        iteration++;
	        password += String.fromCharCode(randomNumber);
	    }
	    return password;
	}
});