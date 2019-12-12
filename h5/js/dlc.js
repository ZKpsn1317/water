/**
 * Created by Administrator on 2017/5/24.
 */
//FastClick.attach(document.body);
function showMask(opera){
    $('#mask').remove();
    $('body').append('<div id="mask"'+(opera==1?"onclick=\'dlctipbox.clear()\'":"")+'style="position: fixed;cursor:pointer;height: 100%;width: 100%;top:0;left:0;background:rgba(0,0,0,0.6);z-index: 100;"></div>');
}
function hideMask(){
    $('#mask').remove();
}
//数据为空提示
function emptyTip(tip){
    return '<div class="h20"></div><div class="empty"><div class="logo"><img src="../img/empty_page_nothing.png"></div><div class="msg" style="color: #999;">'+tip+'</div></div>';
}
//判断身份证格式是否正确
function checkIdCode(value){
    var value = $.trim(value);
    var userCardreg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
    var taiwanreg=/^[A-Z][0-9]{9}$/;   //台湾
    var xianggangreg=/^[A-Z][0-9]{6}\([0-9A]\)$/; //香港
    var aomenreg=/^[157][0-9]{6}\([0-9]\)$/;   //澳门
    //return /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(value);
    return (userCardreg.test(value)||taiwanreg.test(value)||xianggangreg.test(value)||aomenreg.test(value));
}

//判断是否为空
function isNull(value){
    if($.trim(value).length){
        return false;
    }else{
        return true;
    }
}
//判断手机或者电话号码格式
function checkMobileAndTel(value){
    return /^1(3|4|5|7|8)\d{9}$/.test(value);
};
//多张图片上传，配合表单使用
function upLoadImg(file,picbox,size){
    var size = size?size:1;
    if($('.shade').length == 0){
        $('body').append($('<div class="shade" onclick="javascript:closeShade()" style="position: absolute;width: 100%;height: 100%;top: 0;left: 0;background: rgba(0, 0, 0, 0.2);z-index: 1000;display: none;"><div class="" style="width: 300px;height: 200px;line-height: 200px;position: absolute;top: 50%;left: 50%;margin-top: -100px;margin-left: -150px;background: white;border-radius: 5px;text-align: center;"><span class="text_span"></span></div></div>'));
    }
    if($('.shadeImg').length == 0) {
        $('body').append($('<div class="shadeImg" onclick="javascript:closeShadeImg()" style="position: absolute;display: none;width: 100%;height: 100%;top: 0;left: 0;z-index: 15;text-align: center;background: rgba(0, 0, 0, 0.2);"><div><img class="showImg" src="" style="width: 100%;margin-top: 140px;"></div></div>'));
    }
    var objUrl;
    var img_html;
    var template = $(file).parent().html();
    var picbox = document.getElementById(picbox);
    var filepath = $(file).val();
    if($(picbox).children('label').length > size){
        $(".shade").fadeIn(500);
        $(".text_span").text("最多可以上传"+size + '张图片');
        return false;
    };
    for(var i = 0; i < file.files.length; i++) {
        objUrl = getObjectURL(file.files[i]);
        var extStart = filepath.lastIndexOf(".");
        var ext = filepath.substring(extStart, filepath.length).toUpperCase();
        if(ext != ".BMP" && ext != ".PNG" && ext != ".GIF" && ext != ".JPG" && ext != ".JPEG") {
            $(".shade").fadeIn(500);
            $(".text_span").text("图片限于bmp,png,gif,jpeg,jpg格式");
            return false;
        } else {
            img_html = "<div class='isImg' style='height: 100%'><img src='" + objUrl + "' onclick='javascript:lookBigImg(this)' style='height: 100%; width: 100%;border-radius: .1rem;' /><button class='removeBtn' onclick='javascript:removeImg(this)' style='position:absolute;right: 0;top: 0;background: rgba(0,0,0,0.2);color: #fff;border-radius: 50%;width: 0.3rem;height: 0.3rem;font-size: 0.1rem;display: flex;align-items: center;justify-content: center;'>x</button></div>";
            $(file).parent().append(img_html);
            var obj = $('<label class="a-upload fl" style="position: relative">'+template+'</label>');
            $(picbox).append(obj);
        }
    }
    return true;
}

function getObjectURL(file) {
    var url = null;
    if(window.createObjectURL != undefined) {
        url = window.createObjectURL(file);
    } else if(window.URL != undefined) {
        url = window.URL.createObjectURL(file);
    } else if(window.webkitURL != undefined) {
        url = window.webkitURL.createObjectURL(file);
    }
    return url;
}
function removeImg(r){
    $(r).parents('label').remove();
    event.stopPropagation();
    event.preventDefault();
    return false;
}
function lookBigImg(b){
    $(".shadeImg").fadeIn(500);
    $(".showImg").attr("src",$(b).attr("src"));
    event.stopPropagation();
    event.preventDefault();
    return false;
}
function closeShade(){
    $(".shade").fadeOut(500);
}
function closeShadeImg(){
    $(".shadeImg").fadeOut(500);
};
/*图片上传end*/
/*日期格式化*/
function formatDate(now,ff) {
    var year=now.getFullYear();
    var month=now.getMonth()+1<10?'0'+(now.getMonth()+1):now.getMonth()+1;
    var date=now.getDate()<10?'0'+now.getDate():now.getDate();
    var hour=now.getHours()<10?'0'+now.getHours():now.getHours();
    var minute=now.getMinutes()<10?'0'+now.getMinutes():now.getMinutes();
    var second=now.getSeconds()<10?'0'+now.getSeconds():now.getSeconds();
    if(ff=='Y-m-d'){
        return year+"-"+month+"-"+date;
    }else if(ff=='Y-m-d H:i:s'){
        return year+"-"+month+"-"+date+" "+hour+":"+minute+":"+second;
    }else if(ff=='Y-m-d H:i'){
        return year+"-"+month+"-"+date+" "+hour+":"+minute;
    }
}
function format(time,ff){
    time=time.toString();
    if(time.length==10)time=time*1000;
    var d=new Date(time);
    return formatDate(d,ff);
}
//加载中
function loadingShow(str){
	var str=str?str:'加载中...';
    $('.loading').remove();
    var str = '<div class="loading"><div class="spinner5"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div><div class="bounce4"></div></div><div class="loadingTip" style="margin-top: 0.2rem;">'+str+'</div></div>';
    $('body').append(str);
}
//关掉加载
function loadingHide(){
    $('.loading').remove();
}
function showLoading(msg){
    if(document.getElementsByClassName('masks').length == 0){
        var str='<div class="masks"><div style="position: fixed;top: 0;left: 0;width: 100%;height: 100%;background:#fff;z-index: 99;"></div>;<div style="position: absolute;top: 4rem;left: 0;z-index: 100;width:100%;text-align: center;"><img src="../img/loading.gif" style="width: 1.8rem;"/><p style="margin-top: .2rem;" id="masks_msg">'+(msg?msg:'')+'</p></div></div>'
        $('body').append(str);
    }else{
        $('#masks_msg').text(msg?msg:'');
    }
}

function hideLoading(){
    $('.masks').remove();
}
/**
 * 获取url参数
 */
function getUrlParam(name, explode, url){
    var param = window.location.search.substr(1);
    if(url){
        if(explode){
            param = url.substr(url.indexOf(explode) + 1);
        }else{
            param = url.substr(url.indexOf('?') + 1);
        }
    }else{
        if(explode){
            param = window.location.href.substr(window.location.href.indexOf(explode) + 1);
        }
    }
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
    var r = param.match(reg);
    if (r != null) return unescape(r[2]); return '';
}
//判断checkbox被选中的个数
function checkedLong(str){
    return $(str+":checked").length;
}
function dlcUrl(){
    return 'http://gzqslshj.app.xiaozhuschool.com';
}
function dlc_request(method, data, cb,async,type){
    var data = data || {};
    url = dlcUrl()+method;
    $.ajax({
        type: type?type:'post',
        url: url,
        data: data,
        dataType: 'json',
        crossDomain:true,
        async:async==false?false:true,
        success:function(res){
            // if(res.code=='401'){
            //     clear_token();
            //     dlctipbox.clear();
            //     clearLocalData('fd_id');
            //     dlctipbox.alert('登录已过期',function (flag) {
            //         if(flag==1){
            //             exitApp();
            //         }
            //     });
            //     return false;
            // }
            if(cb)cb(res);
        },
        error:function(err){
            if(err)cb(err)
        }
    });
}


var dlctipbox = {
    success:function(msg,img){
        if(!document.getElementById('mask')){showMask(1);}
        var html = '<div id="success" class="bw border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 5.2rem;left: 50%;margin-left: -2.6rem;overflow: hidden;">'+
            '<div class="mt60">'+
            '<img src="'+(img?img:'../img/success.png')+'" style="width: 1.4rem;">'+
            '</div>'+
            '<p class="font16 mt40 mb60" style="color: #0fdab6;">'+msg+'</p>'+
            '</div>';
        $('body').append(html);
        $('#success').fadeIn(300);
    },
    error:function(msg,img){
        if(!document.getElementById('mask')){showMask(1);}
        var html = '<div id="error" class="bw border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 5.2rem;left: 50%;margin-left: -2.6rem;overflow: hidden;">'+
            '<div class="mt60">'+
            '<img src="'+(img?img:'../img/error.png')+'" style="width: 1.4rem;">'+
            '</div>'+
            '<p class="font16 mt40 mb60 color2">'+msg+'</p>'+
            '</div>';
        $('body').append(html);
        $('#error').fadeIn(300);
    },
    clear: function(){
        $('#success,#tooltipbox_show_div,#dlctipbox_loading,#alert_img,#mask,#alert,#confirm_img,#confirm,#error').remove();
    },
    confirm_img:function(msg,cb){
        if(!document.getElementById('mask')){showMask(1);}
        var html = '<div id="confirm_img" class="bw border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 6.5rem;left: 50%;margin-left: -3.25rem;overflow: hidden;">'+
            '<div class="mt60">'+
            '<img src="../img/error.png" style="width: 1.4rem;">'+
            '</div>'+
            '<p class="font17 ptb30 lh60 plr40 color2">'+msg+'</p>'+
        '<div class="font16 lh100 flex_a tac bdt">'+
            '<a class="flex1 confirm_btn" onclick="dlctipbox.confirm_img_callback(0)">联系管理员</a>'+
            '<a class="colw bgc1 flex1 confirm_btn" onclick="dlctipbox.confirm_img_callback(1)">提交异常</a>'+
            '</div>'+
            '</div>';
        this.confirm_img_callback = function(flag){
            dlctipbox.clear();
            if(flag == 1){cb(1)}else{cb(0)}
        };
        $('body').append(html);
        $('#confirm_img').fadeIn(300);
    },
    confirm:function(msg,cb){
        if(!document.getElementById('mask')){showMask(1);}
        var html = '<div id="confirm" class="bw border-r1 pos_f dpn" style="z-index:9527;top: 3.4rem;width: 6rem;left: 50%;margin-left: -3rem;overflow: hidden;">'+
            '<p class="font17 ptb60 lh60 plr40 tac">'+msg+'</p>'+
            '<div class="font16 lh100 flex_a tac bdt">'+
            '<a class="flex1" onclick="dlctipbox.confirm_callback(0)">取消</a>'+
            '<a class="colw bgc1 flex1" onclick="dlctipbox.confirm_callback(1)">确定</a>'+
            '</div>'+
            '</div>';
        this.confirm_callback = function(flag){
            dlctipbox.clear();
            //alert(flag)
            if(flag == 1){cb(1)}else{cb(0)}
        };
        $('body').append(html);
        $('#confirm').fadeIn(300);
        
    },
    loading:function(msg){
        var msg=msg?msg:'请稍后...';
        if($('#dlctipbox_loading').length>0){
            $('#dlctipbox_loading_msg').text(msg);
        }else{
            var str='<div style="background: rgba(0,0,0,0.6);width: 2rem;z-index:999999;height: 2rem;position: fixed;left: 50%;top: 50%;margin-left: -1rem;margin-top: -1rem;" class="flex flex_column" id="dlctipbox_loading">'+
                '<div class="loadEffect">'+
                '<span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span>'+
                '</div>'+
                '<p style="color: #fff;font-size: .28rem;margin-top: .1rem;text-align: center;" id="dlctipbox_loading_msg">'+msg+'</p>'+
                '</div>';
            $(document.body).append(str);
        }
    },
    show: function(msg, position, duration, keep) {
        if(!keep)this.clear();
        var msg = msg?msg:'请稍后...';
        if(!msg){
            var m=document.getElementById('tooltipbox_show_div');
            var d = 0.2;
            m.style.webkitTransition = '-webkit-transform ' + d + 's ease-in, opacity ' + d + 's ease-in';
            m.style.opacity = '0';
            setTimeout(function() {
                try{ document.body.removeChild(m); }catch(e){}
            }, d * 1000);
            return;
        }
        if(position!='bottom' && position!='middle' && position!='top'){
            position ='bottom';
        }

        duration = isNaN(duration) ? 1000 : duration;
        var m = document.createElement('div');
        m.id = 'tooltipbox_show_div';
        m.innerHTML = msg;
        var css = "width:60%; font-size:14px;min-width:150px; background:#000; opacity:0.7; min-height:35px; overflow:hidden; color:#fff; line-height:35px; text-align:center; border-radius:5px; position:fixed; left:20%; z-index:999999;";
        if(position=='top'){
            css+="top:10%; ";
        } else if(position=='bottom'){
            css+="bottom:10%; ";
        } else if(position=='middle'){
            css+="top:50%;margin-top:-18px;";
        }
        m.style.cssText = css;
        document.body.appendChild(m);
        if(duration!=0){
            setTimeout(function() {
                var d = 0.2;
                m.style.webkitTransition = '-webkit-transform ' + d + 's ease-in, opacity ' + d + 's ease-in';
                m.style.opacity = '0';
                setTimeout(function() {
                    try{ document.body.removeChild(m); }catch(e){}
                }, d * 1000);
            }, duration);
        }
    },
    alert_img:function(msg,img){
        if(!document.getElementById('mask')){showMask(1);}
        var html = '<div id="alert_img" class="bw font16 border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 6.5rem;left: 50%;margin-left: -3.25rem;overflow: hidden;">'+
            '<div class="mt60">'+
            '<img src="'+(img?img:'../img/alert_ico.png')+'" style="width: 1.04rem;">'+
            '</div>'+
            '<p class="mt20" style="color: #0fdab6;">开锁成功</p>'+
            '<p class="lh40 plr40 mb60 mt10">'+msg+'</p>'+
        '<a class="flex_aj bdt color1 lh100" onclick="dlctipbox.clear();">确认</a>'+
            '</div>';
        $('body').append(html);
        $('#alert_img').fadeIn(300);
    },
    alert:function(msg,cb){
        if(!document.getElementById('mask')){showMask();}
        var html = '<div id="alert" class="bw font16 border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 6rem;left: 50%;margin-left: -3rem;overflow: hidden;">'+
            '<p class="lh40 plr40 mt60 mb60 mt10">'+msg+'</p>'+
       '<a class="flex_aj bdt color1 lh100" onclick="dlctipbox.confirm_callback(1)">确定</a>'+
            '</div>';
        this.confirm_callback = function(flag){
            dlctipbox.clear();
            if(flag == 1){if(cb)cb(1)}else{if(cb)cb(0)}
        };
        $('body').append(html);
        $('#alert').fadeIn(300);
    },
    toast:function(msg,cb,img){
        var html = '<div class="masks" style="width: 100%;background: #000;height: 100%;position: fixed;left: 0;top: 0;opacity: 0.5;"></div><div id="dlc_toast" class="bw font16 border-r1 pos_f tac dpn" style="z-index:9527;top: 3.4rem;width: 4.6rem;left: 50%;margin-left: -2.3rem;overflow: hidden;">'+
            '<div class="mt30">'+
            '<img src="'+(img?img:'../img/true.png')+'" style="width: 1.04rem;">'+
            '</div>'+
            '<p class="lh40 plr40 mb60 mt20">'+msg+'</p>'+
            '</div>';
        $('body').append(html);
        $('#dlc_toast').fadeIn(300);
        setTimeout(function(){
            $('#dlc_toast').remove();
            if(cb)cb({flag:1})
        },3000);
    },
};

function user_save(token){//保存token
    localStorage.setItem('token', token);
    if(!token)localStorage.setItem('user', null);
}
function user_token(){//获取token
    return localStorage.getItem('token');
}

function clear_token(){
	localStorage.removeItem('token');
}

if(!user_token() && getUrlParam('code')==''){
    location.replace("https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx254a03a791c5d883&redirect_uri="+window.location.href+"&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect");
}
if(!user_token() && getUrlParam('code')){
    dlc_request('/wxsite/public/api',{'api_name':'login','code':getUrlParam('code')},function(res){
        console.log(res);
        if(res.code == 1){
            user_save(res.data.token);
        }
    },false);
}

function socket(data,cb){
    if("WebSocket" in window){
        var ws = new WebSocket("ws://123.207.127.201:9111/echo");
        ws.onopen = function(){
            console.log('socket已打开');
            ws.send(data);
        };
        ws.onmessage = function (res){
            cb({'code':1,'data':JSON.parse(res.data)});
        };
        ws.onclose = function(close){
            cb({'code':3,'msg':'连接失败','data':close});
        };
        ws.onerror = function(err){
            cb({'code':4,'msg':'连接错误','data':err});
        };
    }else{
        cb({'code':2,'msg':'当前浏览器不支持WebSocket!'})
    }
}

//微信公众号获取签名用
(function(D){function p(b,e,c){var a=0,d=[0],f="",g=null,f=c||"UTF8";if("UTF8"!==f&&"UTF16"!==f)throw"encoding must be UTF8 or UTF16";if("HEX"===e){if(0!==b.length%2)throw"srcString of HEX type must be in byte increments";g=v(b);a=g.binLen;d=g.value}else if("TEXT"===e)g=w(b,f),a=g.binLen,d=g.value;else if("B64"===e)g=x(b),a=g.binLen,d=g.value;else if("BYTES"===e)g=y(b),a=g.binLen,d=g.value;else throw"inputFormat must be HEX, TEXT, B64, or BYTES";this.getHash=function(b,f,c,e){var g=null,
    h=d.slice(),l=a,n;3===arguments.length?"number"!==typeof c&&(e=c,c=1):2===arguments.length&&(c=1);if(c!==parseInt(c,10)||1>c)throw"numRounds must a integer >= 1";switch(f){case "HEX":g=z;break;case "B64":g=A;break;case "BYTES":g=B;break;default:throw"format must be HEX, B64, or BYTES";}if("SHA-1"===b)for(n=0;n<c;n+=1)h=s(h,l),l=160;else throw"Chosen SHA variant is not supported";return g(h,C(e))};this.getHMAC=function(c,b,e,g,q){var h,l,n,r,p=[],t=[];h=null;switch(g){case "HEX":g=z;break;case "B64":g=
    A;break;case "BYTES":g=B;break;default:throw"outputFormat must be HEX, B64, or BYTES";}if("SHA-1"===e)l=64,r=160;else throw"Chosen SHA variant is not supported";if("HEX"===b)h=v(c),n=h.binLen,h=h.value;else if("TEXT"===b)h=w(c,f),n=h.binLen,h=h.value;else if("B64"===b)h=x(c),n=h.binLen,h=h.value;else if("BYTES"===b)h=y(c),n=h.binLen,h=h.value;else throw"inputFormat must be HEX, TEXT, B64, or BYTES";c=8*l;b=l/4-1;if(l<n/8){if("SHA-1"===e)h=s(h,n);else throw"Unexpected error in HMAC implementation";
    h[b]&=4294967040}else l>n/8&&(h[b]&=4294967040);for(l=0;l<=b;l+=1)p[l]=h[l]^909522486,t[l]=h[l]^1549556828;if("SHA-1"===e)e=s(t.concat(s(p.concat(d),c+a)),c+r);else throw"Unexpected error in HMAC implementation";return g(e,C(q))}}function w(b,e){var c=[],a,d=[],f=0,g;if("UTF8"===e)for(g=0;g<b.length;g+=1)for(a=b.charCodeAt(g),d=[],128>a?d.push(a):2048>a?(d.push(192|a>>>6),d.push(128|a&63)):55296>a||57344<=a?d.push(224|a>>>12,128|a>>>6&63,128|a&63):(g+=1,a=65536+((a&1023)<<10|b.charCodeAt(g)&1023),
    d.push(240|a>>>18,128|a>>>12&63,128|a>>>6&63,128|a&63)),a=0;a<d.length;a+=1)(f>>>2)+1>c.length&&c.push(0),c[f>>>2]|=d[a]<<24-f%4*8,f+=1;else if("UTF16"===e)for(g=0;g<b.length;g+=1)(f>>>2)+1>c.length&&c.push(0),c[f>>>2]|=b.charCodeAt(g)<<16-f%4*8,f+=2;return{value:c,binLen:8*f}}function v(b){var e=[],c=b.length,a,d;if(0!==c%2)throw"String of HEX type must be in byte increments";for(a=0;a<c;a+=2){d=parseInt(b.substr(a,2),16);if(isNaN(d))throw"String of HEX type contains invalid characters";e[a>>>3]|=
    d<<24-a%8*4}return{value:e,binLen:4*c}}function y(b){var e=[],c,a;for(a=0;a<b.length;a+=1)c=b.charCodeAt(a),(a>>>2)+1>e.length&&e.push(0),e[a>>>2]|=c<<24-a%4*8;return{value:e,binLen:8*b.length}}function x(b){var e=[],c=0,a,d,f,g,m;if(-1===b.search(/^[a-zA-Z0-9=+\/]+$/))throw"Invalid character in base-64 string";a=b.indexOf("=");b=b.replace(/\=/g,"");if(-1!==a&&a<b.length)throw"Invalid '=' found in base-64 string";for(d=0;d<b.length;d+=4){m=b.substr(d,4);for(f=g=0;f<m.length;f+=1)a="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".indexOf(m[f]),
    g|=a<<18-6*f;for(f=0;f<m.length-1;f+=1)e[c>>2]|=(g>>>16-8*f&255)<<24-c%4*8,c+=1}return{value:e,binLen:8*c}}function z(b,e){var c="",a=4*b.length,d,f;for(d=0;d<a;d+=1)f=b[d>>>2]>>>8*(3-d%4),c+="0123456789abcdef".charAt(f>>>4&15)+"0123456789abcdef".charAt(f&15);return e.outputUpper?c.toUpperCase():c}function A(b,e){var c="",a=4*b.length,d,f,g;for(d=0;d<a;d+=3)for(g=(b[d>>>2]>>>8*(3-d%4)&255)<<16|(b[d+1>>>2]>>>8*(3-(d+1)%4)&255)<<8|b[d+2>>>2]>>>8*(3-(d+2)%4)&255,f=0;4>f;f+=1)c=8*d+6*f<=32*b.length?c+
"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/".charAt(g>>>6*(3-f)&63):c+e.b64Pad;return c}function B(b){var e="",c=4*b.length,a,d;for(a=0;a<c;a+=1)d=b[a>>>2]>>>8*(3-a%4)&255,e+=String.fromCharCode(d);return e}function C(b){var e={outputUpper:!1,b64Pad:"="};try{b.hasOwnProperty("outputUpper")&&(e.outputUpper=b.outputUpper),b.hasOwnProperty("b64Pad")&&(e.b64Pad=b.b64Pad)}catch(c){}if("boolean"!==typeof e.outputUpper)throw"Invalid outputUpper formatting option";if("string"!==typeof e.b64Pad)throw"Invalid b64Pad formatting option";
    return e}function q(b,e){return b<<e|b>>>32-e}function r(b,e){var c=(b&65535)+(e&65535);return((b>>>16)+(e>>>16)+(c>>>16)&65535)<<16|c&65535}function t(b,e,c,a,d){var f=(b&65535)+(e&65535)+(c&65535)+(a&65535)+(d&65535);return((b>>>16)+(e>>>16)+(c>>>16)+(a>>>16)+(d>>>16)+(f>>>16)&65535)<<16|f&65535}function s(b,e){var c=[],a,d,f,g,m,p,u,k,s,h=[1732584193,4023233417,2562383102,271733878,3285377520];b[e>>>5]|=128<<24-e%32;b[(e+65>>>9<<4)+15]=e;s=b.length;for(u=0;u<s;u+=16){a=h[0];d=h[1];f=h[2];g=h[3];
    m=h[4];for(k=0;80>k;k+=1)c[k]=16>k?b[k+u]:q(c[k-3]^c[k-8]^c[k-14]^c[k-16],1),p=20>k?t(q(a,5),d&f^~d&g,m,1518500249,c[k]):40>k?t(q(a,5),d^f^g,m,1859775393,c[k]):60>k?t(q(a,5),d&f^d&g^f&g,m,2400959708,c[k]):t(q(a,5),d^f^g,m,3395469782,c[k]),m=g,g=f,f=q(d,30),d=a,a=p;h[0]=r(a,h[0]);h[1]=r(d,h[1]);h[2]=r(f,h[2]);h[3]=r(g,h[3]);h[4]=r(m,h[4])}return h}"function"===typeof define&&define.amd?define(function(){return p}):"undefined"!==typeof exports?"undefined"!==typeof module&&module.exports?module.exports=
    exports=p:exports=p:D.jsSHA=p})(this);
function wx_js(signature,timestamp,nonceStr){
    var e=decodeURIComponent('jsapi_ticket='+signature+'&noncestr='+nonceStr+'&timestamp='+timestamp+'&url='+location.href.split("#")[0]),
        s=new jsSHA(e,"TEXT"),
        signature=s.getHash("SHA-1","HEX");
    return signature;
}

function msg_tip(tip,tap,text){
    var text = text?text:'确定';
    if($('.msg_tip').length>0){
        $('.msg_tip').html('<img src="../img/clock.png" style="width: 1.72rem;"/>'+
        '<p class="font16 col3 mt40">'+tip+'</p>'+
        '<a class="bgc1 min_btn colw font15 mt40 '+(tap?tap:'')+'" style="cursor:pointer;">'+text+'</a>');
    }else{
        $('body').append('<div class="status_tip bw tac msg_tip">'+
            '<img src="../img/clock.png" style="width: 1.72rem;"/>'+
            '<p class="font16 col3 mt40">'+tip+'</p>'+
            '<a class="bgc1 min_btn colw font15 mt40 '+(tap?tap:'')+'" style="cursor:pointer;">'+text+'</a>'+
            '</div>');
    }
}
//判断是否未微信或者支付宝浏览器
function isWX_Allipay(){
    if(window.navigator.userAgent.toLowerCase().match(/MicroMessenger/i) == 'micromessenger'){
        return 'WX';
    }else if(window.navigator.userAgent.toLowerCase().match(/AlipayClient/i) == 'alipayclient'){
        return 'Allipay';
    }else{
        return 'Others';
    }
}

function exitApp() {
    if(isWX_Allipay()=='WX'){
        WeixinJSBridge.call('closeWindow');
    }else if(isWX_Allipay()=='Allipay'){
        function ready(callback) {
            // 如果jsbridge已经注入则直接调用
            if (window.AlipayJSBridge) {
                callback && callback();
            } else {
                // 如果没有注入则监听注入的事件
                document.addEventListener('AlipayJSBridgeReady', callback, false);
            }
        }
        ready(function() {
            AlipayJSBridge.call('exitApp');
        });
    }
}

// 带图标提示框
function tipWinShow(str,url) {
    var msg = '<section class="successBox flex_aj">' +
        ' <div class="tipWin flex_aj flex_column">' +
        ' <img src="'+url+'"  style="width: 1.3rem;">'+
        ' <p class="font16 col3 mt30">' + str + '</p>' +
        ' </div>' +
        ' </section>';
    $('body').append(msg);
    $('.successBox').fadeIn(300).css('display', 'flex');
    // setTimeout(function () {
    //     tipWinHide();
    // },1500);
}
//隐藏提示框
function tipWinHide() {
    $('.successBox').fadeOut(300);
}

//保存本地数据
function saveLocalData(name,msg) {
    localStorage.setItem(name,JSON.stringify(msg));
}
//获取保存本地数据
function getLocalData(name) {
    return JSON.parse(localStorage.getItem(name)) || '';
}
//清除保存的本地数据
function clearLocalData(name){
    localStorage.removeItem(name)
}

//监听微信返回按钮ios
function addEventback() {
    pushHistory();
    var bool = false;
    setTimeout(function() {
        bool = true;
    }, 500);
    window.addEventListener("popstate", function(e) {
        if (bool) {
            //alert("我监听到了浏览器的返回按钮事件啦");//根据自己的需求实现自己的功能
            location.reload();
        }
        pushHistory();
    }, false);
}
function pushHistory() {
    var state = {
        title : "",
        url : ""
    };
    window.history.replaceState(state, "", "");
}













