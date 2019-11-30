/*=====================================================
News
======================================================*/
var News = new (function() {
	//googles geo - coder from google maps api. loaded on demand when 
	//a news post requests to show a map
	this.geocoder = null;
	//a reference to all map objects that were recreated
	this.maps = Array();
	
	/*=====================================================
	
	======================================================*/
	this.Init = function() {

	}
	
	this.ShowMapOnLoad = function(mapid, addressString) {
		Util.AddOnLoad(
			function(){News.ShowMap(mapid, addressString);}
		);
	}
	
	this.ShowMap = function(mapid, addressString) {
	
		if (!GBrowserIsCompatible()) {
			return;
		}
		
		if (this.geocoder == null ) 
			this.geocoder = new GClientGeocoder();
			
		var map = new GMap2($("map"+mapid));
		map.addControl(new GLargeMapControl());
		
		this.maps[mapid] = map;
		
		if( typeof addressString != "undefined" ) {
			this.ShowAddress(mapid, addressString);
		}	  	
	}
	
	this.ShowAddress = function(mapid, addressString) {
		var map = this.maps[mapid];
		this.geocoder.getLatLng( 
			addressString,
			function(point) {
				if(!point) {
					//alert("address not found");
					//Util.Hide("map"+mapid);
					var linkUrl = "http://maps.google.com/maps?f=q&hl=en&z=15&q="+addressString;
					var theLink = "<a href=\""+linkUrl+"\" target=\"google maps\">Search Google Maps</a>";
					var html = addressString+"<br />This address <b>could not</b> be found. <br />"+theLink;
					
					//$("map"+mapid).style.display = "none";
					
					//http://maps.google.com/?ie=UTF8&ll=33.735761,-117.752838&spn=0.397429,0.725098&z=11&om=1
					map.setCenter(new GLatLng(33.735761, -117.752838), 10);
					
					var marker = new GMarker(new GLatLng(33.735761, -117.752838));
					map.addOverlay(marker);
					marker.openInfoWindowHtml(html);
					GEvent.addListener(marker, "click", function() {
				    	marker.openInfoWindowHtml(html);
				  	});
					
				} 
				else { 
					map.setCenter(point, 13);
					var marker = new GMarker(point);
					map.addOverlay(marker);
					
					var linkUrl = "http://maps.google.com/maps?f=q&hl=en&z=15&q="+addressString;
					var theLink = "<a href=\""+linkUrl+"\" target=\"google maps\">Get Directions</a>";
					var html = addressString+"<br />"+theLink;
					//marker.openInfoWindowHtml(html);
					GEvent.addListener(marker, "click", function() {
				    	marker.openInfoWindowHtml(html);
				  	});
				}
			}
		);
	}
	
	//function showAddress(address) {  geocoder.getLatLng(    address,    function(point) {      if (!point) {        alert(address + " not found");      } else {        map.setCenter(point, 13);        var marker = new GMarker(point);        map.addOverlay(marker);        marker.openInfoWindowHtml(address);      }    }  );}
	
	
})();

