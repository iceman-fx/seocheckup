// SEO-CheckUp Scripts
// v1.3.4

$(function(){
	//open detail-window	
	$("body").on("click", "a.seoculist-detail", function(e){ e.preventDefault(); showSeocumodal($(this)); });

	//init single-refresh from seoculist
	$("body").on("submit", "form.seoculist-form", function(e){ e.preventDefault(); });
	$("body").on("click", "form.seoculist-form a", function(e){ refreshSeoculist($(this), false, true); });
	
	//init multi-refresh from seoculist
	$("body").on("click", ".seocu-result-refresh", function(e){ $("form.seoculist-form input").each(function(){ var a = $(this).parent().find("a"); a.addClass("rotate"); setTimeout(function(){ refreshSeoculist(a, false, false); }, 250); }); });
	
	//switch infolist
	$("body").on("click", ".seocu-infolistswitch", function(e){ dst = $(this).attr('data-seocu-dst'); $(this).next("div").toggle(); /*$(dst).toggle();*/ });
});

	
function showSeocumodal(clicker)
{	//fill the modalbox with the results/tests
	if (clicker.length) {
		dst = $("#seocu-modal");
			dst.appendTo("body");
		aid = parseInt(clicker.attr('data-seocu-aid'));
		cid = parseInt(clicker.attr('data-seocu-cid'));
			cid = (cid > 0 ? cid : 1);
		aname = clicker.attr('data-seocu-aname');
		
		if (aid > 0) {
			dst.find(".modal-title").html(seoculang_modal.title+": "+aname+" ["+aid+"]");
			dst.find(".modal-body").html('<img src="/assets/addons/'+seoculang_modal.addonname+'/indicator.gif" width="16" height="16" border="0" id="ajax_loading" style="display: block; margin: 0px auto;" />').load("", "rex-api-call=a1544_getSeocheckup&article_id="+aid+"&clang="+cid+"&showtests=1", function(){  });
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
{	//refresh the seoculist entry
	if (btn.length > 0) {
		getcache = (arguments[1] === true ? true : false);
		async = (arguments[2] === false ? false : true);
		var inp = btn.parents("form.seoculist-form").find("input");
		var aid = parseInt(inp.attr('data-seocu-aid'));
		var cid = parseInt(inp.attr('data-seocu-cid'));
		cid = (cid > 0 ? cid : 1);
		
		//console.log("AID: " +aid+ " / CID: " +cid+ " / getcache: " +getcache+ " / async: " +async);
		
		if (aid > 0) {
			if (!getcache) { btn.addClass("rotate"); }
			urldata = "rex-api-call=a1544_getSeocheckup&article_id="+aid+"&clang="+cid+"&keyword="+encodeURIComponent(inp.val())+"&mode=json&getcache="+getcache;
			
			$.ajax({ url: "", async: async, data: urldata, dataType: "json" })
			.done(function(data){
				btn.removeClass("rotate");
				
				result = parseFloat(data["result"]);
					resultcol = "#3BB594";
						resultcol = (result > 70 && result < 90 ? "#CEB964" : resultcol);
						resultcol = (result >= 50 && result <= 70 ? "#F90" : resultcol);
						resultcol = (result > 30 && result < 50 ? "#EC7627" : resultcol);
						resultcol = (result <= 30 ? "#D9534F" : resultcol);
					rhtml = '<div class="seocu-result" style="background: '+resultcol+';">'+result+'/100</div> <div class="seocu-result seocu-result-info">'+seoculang_modal.legibility+': '+parseFloat(data["flesch"])+'</div>';
					rhtml += '<br /><a class="seoculist-detail" data-toggle="modal" data-target="#seocu-modal" data-seocu-aid="'+aid+'" data-seocu-cid="'+cid+'" data-seocu-aname="'+data["article_name"]+'">'+seoculang_modal.detail+'</a>';
				$('#entry'+aid).find('.seoculist-data').html(rhtml);
			})
			.fail(function(jqXHR, textStatus, errorThrown){ btn.removeClass("rotate"); $('#entry'+aid).find('.seoculist-data').html(seoculang_modal.error+':</strong> '+textStatus+'<br /><span class="info">'+errorThrown)+'</span>'; });
		}
	}
}