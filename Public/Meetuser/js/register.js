function submit(){
		var realname=$("input[name='realname']").val().trim();
		if(realname==''){
			$("input[name='realname']").focus();
			return false;
		}
		
		var phone=$("input[name='phone']").val().trim();
		if(phone==''){
			$("input[name='phone']").focus();
			return false;
		}
		var idcard=$("input[name='idcard']").val().trim();
		if(idcard==''){
			$("input[name='idcard']").focus();
			return false;
		}
		var position=$("input[name='position']").val();
		if(position==''){
			$("input[name='position']").focus();
			return false;
		}
		var brand_id=$("select[name='brand_id']").val();
		if(brand_id==''){
			alert("请选择品牌");
			return false;
		}
		var region_id=$("select[name='region_id']").val();
		if(region_id==''){
			alert("请选择大区");
			return false;
		}
		var city_id=$("select[name='city_id']").val();
		if(city_id==''){
			alert("请选择城市");
			return false;
		}
		var store_id=$("select[name='store_id']").val();
		if(store_id==''){
			alert("请选择经销门店");
			return false;
		}
		var p_data={'Mid':$("input[name='Mid']").val(),
				'realname':realname,
				'sex':$("input[name='sex']:checked").val(),
				'phone':phone,
				'idcard':idcard,
				'position':position,
				'brand_id':brand_id,
				'region_id':region_id,
				'city_id':city_id,
				'store_id':store_id,
				'hotel_type':$("input[name='hotel_type']:checked").val(),
				//'house_type':$("input[name='house_type']:checked").val(),
				'checkin_date':$("input[name='checkin_date']").val(),
				'leave_date':$("input[name='leave_date']").val(),
				'food_req':$("input[name='food_req']").val(),
				'roommate_name':$("input[name='roommate_name']").val(),
				};
		$.post(register_config.submit_url,p_data,
				function(result){
					console.log(result);
					if(result.status){
						alert(result.msg);
						console.log(result.success_url);
						location.href=result.success_url;
					}else{
						alert(result.msg);
					}
  		},'json');
}
$(function(){
	//选择品牌
	$("select[name='brand_id']").change(function(){
		var brand_id=$("select[name='brand_id']").val();
		if(brand_id!=''){
			$.post(register_config.get_brand_json,{'brand_id':brand_id},
					function(result){
						//大区
						var region_data=result.data.region_data;
						var r_html="<option value=\"\">请选择大区</option>";
						if(region_data!=null){
							 for(var i=0;i<region_data.length;i++){
								 r_html+="<option value=\""+region_data[i].id+"\">"+region_data[i].region_name+"</option>";
							} 
						}else{
							$("select[name='region_id']").val("");
						}
						$("select[name='region_id']").html(r_html);
	  		},'json');
		}
	});
	//选择大区
	$("select[name='region_id']").change(function(){
		var region_id=$("select[name='region_id']").val();
		if(region_id!=''){
			$.post(register_config.get_region_json,{'region_id':region_id},
					function(result){
						//城市
						var city_data=result.data.city_data;
						var c_html="<option value=\"\">请选择城市</option>";
						if(city_data!=null){
							 for(var i=0;i<city_data.length;i++){
								 c_html+="<option value=\""+city_data[i].id+"\">"+city_data[i].city_name+"</option>";
							} 
						}else{
							$("select[name='city_id']").val("");
						}
						$("select[name='city_id']").html(c_html);
	  		},'json');
		}
	});
	//选择城市
	$("select[name='city_id']").change(function(){
		var city_id=$("select[name='city_id']").val();
		if(city_id!=''){
			$.post(register_config.get_city_json,{'city_id':city_id},
					function(result){
						//门店
						var store_data=result.data.store_data;
						var s_html="<option value=\"\">请选择经销门店</option>";
						if(store_data!=null){
							 for(var i=0;i<store_data.length;i++){
								 s_html+="<option value=\""+store_data[i].id+"\">"+store_data[i].store_name+",代码："+store_data[i].store_code+"</option>";
							} 
						}else{
							$("select[name='store_id']").val("");
						}
						$("select[name='store_id']").html(s_html);
						
						
	  		},'json');
		}
	});
	
	$("input[name='hotel_type']").change(function(){
		var val=$(this).val();
		if(val==1){
			$("#roommate_name").show();
		}else{
			$("#roommate_name").hide();
		}
	});
	
	
});