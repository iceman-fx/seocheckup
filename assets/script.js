// SEO-CheckUp Scripts
// v1.8.1

$(function(){
	$seocu_multicount = 0;
	
	//open detail/wdf-window
	$("body").on("click", "a.seoculist-detail", function(e){ e.preventDefault(); showSeocumodal($(this), 'detail'); });
	$("body").on("click", "a.seoculist-morewdf", function(e){ e.preventDefault(); showSeocumodal($(this), 'wdf'); });

	//init single-refresh from seoculist
	$("body").on("submit", "form.seoculist-form", function(e){ e.preventDefault(); });
	$("body").on("click", "form.seoculist-form a", function(e){ refreshSeoculist($(this), false, true); });
	
	//init multi-refresh from seoculist
	$("body").on("click", ".seocu-result-refresh", function(e){ $(this).addClass("rotate"); dst = $("form.seoculist-form input"); $seocu_multicount = dst.length; dst.each(function(){ var a = $(this).parent().find("a"); a.addClass("rotate"); setTimeout(function(){ refreshSeoculist(a, false, false); }, 250); }); });
	
	//switch infolist
	$("body").on("click", ".seocu-infolistswitch", function(e){ $(this).next("div").toggle(); });
});

	
function showSeocumodal(clicker, content)
{	//fill the modalbox with the results/tests
	var msel = "#seocu-modal";
		
	if (clicker.length) {
		dst = $("#seocu-modal");
			if (!$('body > '+msel).length) { dst.appendTo("body"); }
			
		if (content == 'wdf') { dst.addClass('seocu-modal-large'); }
		else { dst.removeClass('seocu-modal-large'); }		
			
		var aid = parseInt(clicker.attr('data-seocu-aid'));
		var cid = parseInt(clicker.attr('data-seocu-cid'));
			cid = (cid > 0 ? cid : 1);
		var aname = clicker.attr('data-seocu-aname');
		var url = clicker.attr('data-seocu-url');
			url = (url != undefined && url.length > 10 ? url : false);

		//console.log("URL: " +url);
		
		var api = (content == 'wdf' ? 'a1544_getSeocheckupWDF' : 'a1544_getSeocheckup');
			api += (url ? '&checkupmode=url&url='+encodeURIComponent(url) : '');
		
		if (aid > 0 || url) {
			dst.find(".modal-title").html("<strong>"+seoculang_modal.title+"</strong><br>"+aname+" ["+aid+"]");
			dst.find(".modal-body").html('<img src="/assets/addons/'+seoculang_modal.addonname+'/indicator.gif" width="16" height="16" border="0" id="ajax_loading" style="display: block; margin: 0px auto;" />').load("", "rex-api-call="+api+"&article_id="+aid+"&clang="+cid+"&showtests=1", function(){  });
		} else { dst.find(".modal-title").html(seoculang_modal.artnotfound); }
		
		resizeSeocumodal(dst);
		$(window).on("resize", function(){ resizeSeocumodal(dst); });
	}
}


function resizeSeocumodal(dst)
{	//resize the modalbox for correct scrolling
	if (dst.length) { dst.height( $("body").height() ); }
}


function refreshSeoculist(btn)
{	//refresh the seoculist entrys (AJAX)
	if (btn.length > 0) {
		getcache = (arguments[1] === true ? true : false);
		async = (arguments[2] === false ? false : true);
		var inp = btn.parents("form.seoculist-form").find("input");
		var aid = parseInt(inp.attr('data-seocu-aid'));
		var cid = parseInt(inp.attr('data-seocu-cid'));
			cid = (cid > 0 ? cid : 1);
		var url = inp.attr('data-seocu-url');
			url = (url != undefined && url.length > 10 ? url : false);
		
		//console.log("AID: " +aid+ " / CID: " +cid+ " / getcache: " +getcache+ " / async: " +async);
		//console.log("URL: " +url);
		
		var api = 'a1544_getSeocheckup';
			api += (url ? '&checkupmode=url&url='+encodeURIComponent(url) : '');
		
		if (aid > 0 || url) {
			if (!getcache) { btn.addClass("rotate"); }
			urldata = "rex-api-call="+api+"&article_id="+aid+"&clang="+cid+"&keyword="+encodeURIComponent(inp.val())+"&mode=json&getcache="+getcache;
			
			//console.log(urldata);
			
			$.ajax({ url: "", async: async, data: urldata, dataType: "json" })
			.done(function(data){
				dst = $('#entry'+aid);
				
				//stop rotation
				btn.removeClass("rotate");
				$seocu_multicount--;
					if ($seocu_multicount <= 0) { $(".seocu-result-refresh").removeClass("rotate"); }
				
				//console.log(data);
				
				
				//WDF
				var wdflist = "";
				var wdf = data["wdf"];									//object
				
					if (wdf !== null && typeof wdf === "object") {
						wdf_k = Object.keys(wdf);
						wdf_v = Object.values(wdf);
						
						maxwdf = wdf_k.length;
						if (maxwdf > 0) {
							maxwdf = (maxwdf >= 5 ? 5 : maxwdf);
							
							for (var i=0; i<maxwdf; i++) {
								//console.log(wdf_k[i] +" > "+ wdf_v[i]['count']);
								wdflist += wdf_k[i]+'&nbsp;('+wdf_v[i]['count']+')<br>';
							}
							
							wdflist += '<br /><a class="seoculist-morewdf" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'+aid+'" data-seocu-url="'+url+'" data-seocu-cid="'+cid+'" data-seocu-aname="'+data["article_name"]+'">'+seoculang_modal.more+'</a>';
						}
					}
				dst.find('.seoculist-wdf').html(wdflist);				
				
				
				//Title
				title = escapeSeocuHtml(data["seo_title"]);							//string
					title = (title.length < 1 ? '<span class="rex-offline">'+seoculang_modal.title_nok+'</span>' : title);
					title = '<div class="seocu-scroll">'+title+'</div>';
				dst.find('.seoculist-title').html(title);
				
				
				//Desc
				desc = escapeSeocuHtml(data["seo_desc"]);							//string
					desc = (desc.length < 1 ? '<span class="rex-offline">'+seoculang_modal.desc_nok+'</span>' : desc);
					desc = '<div class="seocu-scroll">'+desc+'</div>';
				dst.find('.seoculist-desc').html(desc);
				
				
				//H1
				h1 = data["h1"];													//array
					h1 = (Array.isArray(h1) && h1.length > 0 ? h1.join('<br><br>') : '<span class="rex-offline">'+seoculang_modal.h1_nok+'</span>');
					h1 = '<div class="seocu-scroll">'+h1+'</div>';
				dst.find('.seoculist-h1').html(h1);
				
				
				//H2
				h2 = data["h2"];													//array
					h2 = (Array.isArray(h2) && h2.length > 0 ? h2.join('<br><br>') : '-');
					h2 = '<div class="seocu-scroll">'+h2+'</div>';
				dst.find('.seoculist-h2').html(h2);
				
				
				//Links
				links_int = parseInt(data["link_count_int"]);						//int
				links_ext = parseInt(data["link_count_ext"]);						//int
				dst.find('.seoculist-links').html(links_int+"/"+links_ext);
				
				
				//Words
				words = parseInt(data["word_count"]);								//int
				dst.find('.seoculist-words').html(words);
				
				
				//Flesch
				flesch = parseFloat(data["flesch"]);								//float
					flesch = (isNaN(flesch) ? 0 : flesch);
					
				
				//SEO-Result
				result = parseFloat(data["result"]);								//float
					resultcol = "col1";
						resultcol = (result > 70 && result < 90 	? "col2" : resultcol);
						resultcol = (result >= 50 && result <= 70 	? "col3" : resultcol);
						resultcol = (result > 30 && result < 50 	? "col4" : resultcol);
						resultcol = (result <= 30 					? "col5" : resultcol);
						
				rhtml = '<div class="seocu-result seocu-result-'+resultcol+'bg">'+result+'/100</div> <div class="seocu-result seocu-result-info">'+seoculang_modal.legibility+': '+flesch+'</div>';
					rhtml += '<br /><a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'+aid+'" data-seocu-url="'+url+'" data-seocu-cid="'+cid+'" data-seocu-aname="'+data["article_name"]+'">'+seoculang_modal.detail+'</a>';
				dst.find('.seoculist-data').html(rhtml);
				
				
				//Snippet
				
				
			})
			.fail(function(jqXHR, textStatus, errorThrown){ btn.removeClass("rotate"); $('#entry'+aid).find('.seoculist-data').html(seoculang_modal.error+':</strong> '+textStatus+'<br /><span class="info">'+errorThrown)+'</span>'; });
		}
	}
}


function createSeocuchart()
{	//create barchart from wdf-data
	var csel = "#seocu-wdfchart";
	
	if ($(csel).length > 0) {
		var ctx = $(csel);
		
		/* LINE-Chart */
		var wdfplot = new Chart(ctx, {
			type: 'line',
			data: {
		        labels: wdf_names,
				datasets: [{
		            label: 'WDF',
					backgroundColor: 'rgba(75, 154, 217, 0.33)',
					borderColor: 'rgb(75, 154, 217)',
					data: wdf_counts,
					fill: 'start',
					lineTension: 0.1
        		}]
			},
			options: {
				legend: { display: false },
				scales: { yAxes: [{ ticks: { beginAtZero: true } }] },
				responsive: true,
				spanGaps: false,
				plugins: {
					filler: { propagate: false }
				}
			}
		});
	}
}


function escapeSeocuHtml(str) {
  return str
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}