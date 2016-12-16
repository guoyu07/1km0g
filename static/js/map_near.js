$(function(){
	var default_lat = $CONF.lat;
	var default_lng = $CONF.lng;
	var map = new BMap.Map("map");            // 创建Map实例


	//添加自行车位置
	$.each($CONF.bike, function(k, v){
		
		//创建标注
		var icon_top_offset = 2;
		if(v.availBike == 0) {
			//没车了
			icon_top_offset = 0;
		} else if(v.capacity == v.availBike) {
			//车满了
			icon_top_offset = 4;
		} else if(v.availBike / v.capacity > 0.6) {
			//剩余车占到60%以上
			icon_top_offset = 3;
		} else if(v.availBike / v.capacity < 0.4) {
			//剩余车占40%以下
			icon_top_offset = 1;
		}

		var icon_img = "/static/img/geo-point-bike.png?ver=" + $CONF.tpl_version;
		var myIcon = new BMap.Icon(icon_img, new BMap.Size(30,30));
		myIcon.setImageOffset(new BMap.Size(0, -icon_top_offset*30));
		var marker = new BMap.Marker(new BMap.Point(v.lat, v.lng), {"icon":myIcon});
		map.addOverlay(marker);
		console.log(marker.getIcon().imageOffset);

		//标注提示文案
		var opts = {
    		title : v.id + "." + v.name + " 【剩】" + v.availBike + " 【空】" + (v.capacity - v.availBike), // 信息窗口标题
    		enableAutoPan : false //自动平移
    	}
    	var infoWindow = new BMap.InfoWindow("地址:" + v.address, opts);  // 创建信息窗口对象
		
		//当前车位，定位到中心，打开窗口
		if($CONF.bikeid == v.id) {
			map.openInfoWindow(infoWindow, marker.getPosition());
			default_lat = v.lat;
			default_lng = v.lng;
		}
		marker.addEventListener("click", function(){          
    		map.openInfoWindow(infoWindow, marker.getPosition()); //开启信息窗口
    	});
	});
	
	//定位地图中心
	var point = new BMap.Point(default_lat, default_lng); // 创建点坐标
	map.centerAndZoom(point, 16);                 // 初始化地图,设置中心点坐标和地图级别。
	map.addControl(new BMap.ZoomControl());      //添加地图缩放控件

	//添加当前位置
	var pt = new BMap.Point($CONF.lat, $CONF.lng);
	var myIcon = new BMap.Icon("/static/img/geo-point-current.png?ver=" + $CONF.tpl_version, new BMap.Size(30,30));
	var marker = new BMap.Marker(pt, {"icon":myIcon});
	map.addOverlay(marker);

});

