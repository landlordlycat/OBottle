var m=window.location.href;
var s=m.split('#');
var rh=m;
var nowpage=0;
var pagetype='normal';
var cpage=1;
var nextkey;
document.getElementById('l').style.display='none';
/*预加载页头页尾*/
getp('header','h');/*页头*/
getp('footer','f');/*页尾*/
if(s[1]!==null&&s[1]!==undefined&&s[1]!==''){
	if(s[1].indexOf('!')!==-1){/*如果是文章或者页面*/
			pagetype='popage';
		}else if(s[1].indexOf('?')!==-1){/*如果是搜索*/
			pagetype='search';
		}else{
			pagetype='normal';
		}
	nowpage=0;
	getp(s[1],'c');
	nextkey='';
	cpage=1;
}else{
	window.location.href=m+'#m';
}
function checkurl(){
	pagetype='normal';
	var i=window.location.href;
	if(i!==rh){
		rh=i;
		var t=i.split('#');
		if(t[1].indexOf('!')!==-1){/*如果是文章或者页面*/
			pagetype='popage';
		}else if(t[1].indexOf('?')!==-1){/*如果是搜索*/
			pagetype='search';
		}else{
			pagetype='normal';
		}
		nowpage=0;
		cpage=1;
		getp(t[1],'c');
	}
}
onhashchange=function(){checkurl();};
setInterval(checkurl,1000);
function getp(page,e){
	/*预载入页码*/
	var cswitch=false;
	if(pagetype=='normal'){
		var apage=page.split('/');
        if(apage[1]!==null&&apage[1]!==undefined&&apage[1]!==''&&apage[0]=='m'){
		nowpage=Number(apage[1]);
		page='m';
		}
	}else if(pagetype=='popage'){
		var apage=page.split('!');
        if(apage[1]!==null&&apage[1]!==undefined&&apage[1]!==''){
		page=apage[1];
		var cswitch=true;
		}
	}
	document.getElementById('l').style.display='block';
	var c=document.getElementById(e);
	c.style.opacity=0;
	$.ajax({
        type: "post",
        url: './c/g.php?type=getpage',
        data: {p:page,load:nowpage,mode:pagetype},
        dataType: "text",
        success: function(msg){
          var datat='';
          if(msg!=''){
            datat = eval("("+msg+")");
          }
		  data=datat;
		  if(data.result=='ok'){
			  $('#'+e).html(data.r);
			  if(data.r.match(/^[ ]+$/)){
				  $('#'+e).html('<center><h2 style=\'color:#AAA;\'>QAQ 404</h2></center>');
			  }
			  if(page.indexOf('m')!==-1){
				  allnum=data.allp;
				  if((Number(allnum)-1)<=nowpage){/*数组count比实际数量多1*/
					  setTimeout(function(){$('#loadmore').remove();},10);
				  }
				  cpage+=1;
				  nowpage+=1;
			  }
		  }else{
			  $('#'+e).html('<center><h2 style=\'color:#AAA;\'>'+data.msg+'</h2></center>');
		  }
		  $('#'+e).animate({opacity:'1'});
	  document.getElementById('l').style.display='none';
        },
        error:function(msg){
			$('#'+e).html('<center><h2 style=\'color:#AAA;\'>失去连接~OAO</h2></center>');
        }
      });
}
function getmore(){/*加载更多-函数*/
if(cpage<3){/*自动换页*/
$('#loadmore').remove();
    var e='c';
	document.getElementById('l').style.display='block';
	var c=document.getElementById(e);
	c.style.opacity=0;
	$.ajax({
        type: "post",
        url: './c/g.php?type=getmore',
        data: {load:nowpage},
        dataType: "text",
        success: function(msg){
          var datat='';
          if(msg!=''){
            datat = eval("("+msg+")");
          }
		  data=datat;
		  if(data.result=='ok'){
			  allnum=data.allp;
				  if((Number(allnum)-1)<=nowpage){/*数组count比实际数量多1*/
					  setTimeout(function(){$('#loadmore').remove();},10);
				  }else{
				  nowpage+=1;
				  }
				  cpage+=1;
			  document.getElementById(e).innerHTML=document.getElementById(e).innerHTML+data.r;
		  }else{
			  document.getElementById(e).innerHTML=document.getElementById(e).innerHTML+'<center><h2 style=\'color:#AAA;\'>'+data.msg+'</h2></center>';
		  }
		  $('#'+e).animate({opacity:'1'});
	  document.getElementById('l').style.display='none';
        },
        error:function(msg){
			alert('加载失败：error#1');
        }
      });
}else{
	cpage=0;
	window.open('#m/'+nowpage,'_self');
}
}