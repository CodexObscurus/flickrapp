<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<?php 
if (isset($_POST['tag']) && !empty($_POST['tag']) && $_POST['tag']!=' ') {
    $_SESSION["tag"] = $_POST["tag"];
	$tag = $_SESSION["tag"];
} else {
	$tag = ''; unset($_SESSION["tag"]);
}
if(isset($_SESSION["tag"]) && $_SESSION["tag"]!=''){$msg = '<p>You searched for: <b><i>"'.$_SESSION["tag"].'"</i></b></p>';}
?>
<title>Flickr App</title>
<link href="colorbox/colorbox.css" rel="stylesheet" type="text/css" media="screen" />
<link rel="stylesheet" type="text/css" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
<link href="css/flickrapp.css" rel="stylesheet" type="text/css" media="screen" />
<!--[if IE]><script src="https://github.com/aFarkas/html5shiv/blob/master/dist/html5shiv.js"></script><![endif]-->
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="colorbox/jquery.colorbox-min.js"></script>
<script>
var itemArray = [];
(function($) {
var showitem=0;
	$.fn.jflickrfeed = function(settings, callback) {
		settings = $.extend(true, {flickrbase:'http://api.flickr.com/services/feeds/photos_public.gne', limit:4, qstrings:{tags:'<?php echo $tag; ?>', format:'json', jsoncallback:'?'}, cleanDescription:true, useTemplate:true, itemTemplate:'', itemCallback:function(){}}, settings);

		var first = true; var url = settings.flickrbase + '?';
		for(var key in settings.qstrings){
			if(!first)
				url += '&';
			url += key + '=' + settings.qstrings[key];
			first = false;
		}

		return $(this).each(function(){
			var $container = $(this); var container = this; 
			$.getJSON(url, function(data){
				$.each(data.items, function(i,item){
					if(itemArray.indexOf(item.link)==-1){
					itemArray.push(item.link); showitem=1;
					if(i < settings.limit){
						if(item.title.length>11){item.title = item.title.substr(0,8)+"<a href='' title='"+item.title+"' class='nobox'>...<br />(hover for full)</a>";}
						if(item.title=='' || item.title==' ' || item.title=='.' || item.title=='image' || item.title==undefined){item.title='No title';}
							
						if(settings.cleanDescription){
							var regex = /<p>(.*?)<\/p>/gi;
							var input = item.description;
							if(regex.test(input)){
								item.description = input.match(regex)[2];
								var cleandesc = input.match(regex)[2]; 
								if(item.description!=undefined){
									cleandesc = item.description.replace(/<\/?a[^>]*>/g, "").replace(/<p>/g, "").replace(/<(.*?)p>/g, "").replace(/<br(.*?)>/g, "");									
									if(cleandesc.length>11){cleandesc = cleandesc.substr(0,8)+"<a href='#' title='"+cleandesc+"' class='nobox'>...<br />(hover for full)</a>";}
									item.description = cleandesc;
								} else {
									item.description = 'None';
								}
							}
						}
						
						if(item.tags.length>0){
							var tlist = "";							
							$.each(item.tags, function( t, tag ){
								if(/,/.test(tag)){tag = tag.replace(',', '');};
								tlist += tag;
							});
						} else { tlist = "None"; }
						var shortenedtlist = "<a href='#' title='"+tlist+"' class='nobox'>(hover for full)</a>";
						if(tlist.length<5){item.tags = tlist;}else{item.tags = shortenedtlist;}
						
						item['image_s'] = item.media.m.replace('_m', '_s'); item['image_t'] = item.media.m.replace('_m', '_t'); item['image_m'] = item.media.m.replace('_m', '_m');
						item['image'] = item.media.m.replace('_m', ''); item['image_b'] = item.media.m.replace('_m', '_b');
						delete item.media;
						
						if(settings.useTemplate){
							var template = settings.itemTemplate;
							for(var key in item){
								var rgx = new RegExp('{{' + key + '}}', 'g');
								template = template.replace(rgx, item[key]);
							}
							$container.append(template);
							settings.itemCallback.call(container, item);
						}
					}
					}
				});
				if($.isFunction(callback)){ callback.call(container, data); }
			});
		});
	}
})(jQuery);
var apiurl,myresult,apiurl_size,selected_size,searchurl,searchresult,infourl,inforesult,infolist;

$(document).ready(function(){
$("#infopanel").hide();
$(".infobtn").click(function(){ $("#infopanel").toggle(500); });

document.getElementById('btmCtrls').style.visibility = 'hidden';
var refInt = setInterval(update, 3000); // Refresh Interval
$('.start').click(function(e) {
	e.preventDefault(); e.stopPropagation();
	if (refInt == undefined) {
		refInt = setInterval(update, 3000);
		update();
	};
	return false;
});

$('.stop').click(function(e) {
	e.preventDefault(); e.stopPropagation();
	if (refInt != undefined) {
		clearInterval(refInt);
		refInt = undefined;
	};
	return false;
});

function fillBox(){
	return $('.cbox').jflickrfeed({limit:4,itemTemplate:'<span class="three columns"><a rel="colorbox" href="{{image}}" ><img src="{{image_s}}" alt="{{title}}" /></a><br /><b>Title:</b> {{title}}<br /><b>Author:</b> <a href="https://www.flickr.com/photos/{{author_id}}" class="nobox">{{author_id}}</a><br /><b>Description:</b> {{description}}<br /><b>Tags:</b> {{tags}}</span>'}, function(data) { $('.cbox a:not(".taglink"):not(".start"):not(".stop"):not(".nobox")').colorbox(); scrollDownTo(); });
}

function update(){
	document.getElementById('btmCtrls').style.visibility = 'visible';
	document.getElementById('boxes').appendChild(document.createElement("div").innerHTML = fillBox());
}

function scrollDownTo(){
	$('html, body').animate({ scrollTop: $("#footer").offset().top }, 500);
}
update();
});
</script>
</head>
<body>
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
          <a class="navbar-brand" href="#"><span class='white'>Flickr</span><span class='red'>App</span></a>
        </div>
      </div>
    </nav>

    <div class="jumbotron">
      <div class="container">	  
			<div class="row">
				<div class="four columns whiterightbrdr infobox"><h3><span class="glyphicon glyphicon-wrench"></span>&nbsp;&nbsp;Backend</h3>
        <p><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;Flickr API</p>
		<p><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;PHP</p></div>
				
				<div class="four columns whiterightbrdr infobox"><h3><span class="glyphicon glyphicon-user"></span>&nbsp;&nbsp;Frontend</h3>
		<p><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;HTML5 & CSS</p>
		<p><span class="glyphicon glyphicon-ok"></span>&nbsp;&nbsp;Javascript, jQuery & JSON</p></div>
				
				<div class="four columns infobox"><h3><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;Stream</h3>
		<p><a href='#' class="btn btn-success start"><span class="glyphicon glyphicon-play"></span>&nbsp;&nbsp;Start</a>&nbsp;&nbsp;<a href='#' class="btn btn-danger stop"><span class="glyphicon glyphicon-stop"></span>&nbsp;&nbsp;Stop</a>&nbsp;&nbsp;<a class="btn btn-default infobtn"><i class="glyphicon glyphicon-info-sign"></i></a></p>
		<form name="searchForm" id="searchForm" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>"><input type="text" name="tag" id="searchbox" placeholder="Search tags" /><button type="submit" class="btn btn-default"><i class="glyphicon glyphicon-search" id="searchbtn"></i></button></form><?php echo $msg; ?></div>
			</div>  
			<div class="row" id="infopanel"><h2>Information</h2><p>1. Images that are added to the Flickr public timeline are retrieved and streamed with a 3 second interval*.<br />*Please be aware that this interval may be considerably longer for <i>searched</i> terms, as they are only added to the stream as they are added to Flickr - a common term like 'sky' will show results coming in a little faster than an uncommon term like 'pixelated'.</p><p>2. The feed is automatically scrolled down to follow items being posted, so to inspect a picture's data you may need to stop and then restart the stream (no currently visible images will be lost during this process).</p><p>3. To reset a search (as in remove a search term and just start streaming all images), simply click search (the magnifying glass) again with no search term. This resets the search variable to an empty string.<p align='right'><a href='#' class='btn btn-default infobtn'>Close</a></p></div> 
	  
      </div>
    </div>
<div class="container">

<div id="boxes" class="cbox"></div>

<div class="row" id="btmCtrls">
	<div class="one columns"><a href='#' class="btn btn-success start"><span class="glyphicon glyphicon-play"></span>&nbsp;&nbsp;Start</a></div>
	<div class="one column"></div>
	<div class="one columns"><a href='#' class="btn btn-danger stop"><span class="glyphicon glyphicon-stop"></span>&nbsp;&nbsp;Stop</a></div>
	<div class="nine columns"></div>
</div>

</div>
<div id='footer'>&copy;<?php echo date("Y") ?>&nbsp;<a href="http://www.siteworx.ie/">Siteworx.ie</a></div>
</body>
</html>