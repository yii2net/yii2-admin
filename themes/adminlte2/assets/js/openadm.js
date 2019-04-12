var OA_Menus_Children = [];
var OA_MenusIDs = [];
function oa_build_top_menu() {
    OA_Menus_Children = [];
    OA_MenusIDs = [];
    var top_menu_html = "";
    if(typeof OA_Menus == "object" ){
        for(var i in OA_Menus){
            var top_menu = OA_Menus[i];
            if(top_menu.content.cfg_pid == 0){//说明是顶部菜单
                OA_MenusIDs.push(parseInt(top_menu.content.id));
                if(typeof top_menu.items == "object"){//说明有子菜单
                    top_menu_html += '<li><a id="nav'+top_menu.content.id+'" data-id="'+top_menu.content.id+'" href="#">'+top_menu.content.cfg_comment+'</a></li>';
                    OA_Menus_Children[top_menu.content.id] = top_menu.items;
                }else{
                    top_menu_html += '<li><a id="nav'+top_menu.content.id+'" data-id="'+top_menu.content.id+'" data-label="'+top_menu.content.cfg_comment+'" href="#" data-url="'+top_menu.content.value.url+'" >'+top_menu.content.cfg_comment+'</a></li>';
                }

            }
        }
        if(top_menu_html != ""){
            top_menu_html = '<ul class="nav navbar-nav" id="topmenu">'+top_menu_html+'</ul>';
        }
    }
    if(top_menu_html != ""){
        $('#navbar-collapse').html(top_menu_html);
    }
    oa_top_menu_click();
}

function oa_icon_is_empty(icon) {
    if(typeof icon == "undefined" || icon == "")
        return true;
    return false;
}

function oa_build_left_menu(el) {
    oa_topmenu_change_active(el);
    var topmenu_id = $(el).data('id');
    if(typeof OA_Menus_Children == "object"){
        if(typeof OA_Menus_Children[topmenu_id] == "object"){
            var currentLeftMenuItems = OA_Menus_Children[topmenu_id];
            var sidebar_html = '<ul class="sidebar-menu tree">';
            for(var i in currentLeftMenuItems){
                OA_MenusIDs.push(parseInt(currentLeftMenuItems[i].content.id));
                if(typeof currentLeftMenuItems[i].items == "undefined"){
                    sidebar_html += '<li class="treeview"><a id="nav'+currentLeftMenuItems[i].content.id+'" class="openlink" data-label="'+currentLeftMenuItems[i].content.cfg_comment+'" data-id="'+currentLeftMenuItems[i].content.id+'" href="'+currentLeftMenuItems[i].content.value.url+'"><i class="'+currentLeftMenuItems[i].content.value.icon+'"></i> <span>'+currentLeftMenuItems[i].content.cfg_comment+'</span>';
                    sidebar_html += '</a>';
                }else{
                    sidebar_html += '<li class="treeview"><a id="nav'+currentLeftMenuItems[i].content.id+'" data-label="'+currentLeftMenuItems[i].content.cfg_comment+'" data-id="'+currentLeftMenuItems[i].content.id+'" href="'+currentLeftMenuItems[i].content.value.url+'"><i class="'+( oa_icon_is_empty( currentLeftMenuItems[i].content.value.icon )  ? "fa  fa-angle-right" : currentLeftMenuItems[i].content.value.icon)+'"></i> <span>'+currentLeftMenuItems[i].content.cfg_comment+'</span>';
                    sidebar_html += '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i> </span></a>';
                    var subLeftMenuItems = currentLeftMenuItems[i].items;
                    sidebar_html += '<ul class="treeview-menu">';
                    for(var j in subLeftMenuItems){
                        OA_MenusIDs.push(parseInt(subLeftMenuItems[j].content.id));
                        //判断有没有第三层菜单
                        if(typeof subLeftMenuItems[j].items == "undefined"){
                            sidebar_html += '<li><a id="nav'+subLeftMenuItems[j].content.id+'" class="openlink" data-label="'+subLeftMenuItems[j].content.cfg_comment+'" data-id="'+subLeftMenuItems[j].content.id+'" href="'+subLeftMenuItems[j].content.value.url+'"><i class="'+ ( oa_icon_is_empty( subLeftMenuItems[j].content.value.icon ) ? "fa  fa-angle-right" : subLeftMenuItems[j].content.value.icon) +'"></i> '+subLeftMenuItems[j].content.cfg_comment+'</a></li>';
                        }else{
                            sidebar_html += '<li class="treeview"><a id="nav'+subLeftMenuItems[j].content.id+'" data-label="'+subLeftMenuItems[j].content.cfg_comment+'" data-id="'+subLeftMenuItems[j].content.id+'" href="'+subLeftMenuItems[j].content.value.url+'"><i class="'+ ( oa_icon_is_empty( subLeftMenuItems[j].content.value.icon ) ? "fa  fa-angle-right" : subLeftMenuItems[j].content.value.icon) +'"></i> <span>'+subLeftMenuItems[j].content.cfg_comment+'</span>';
                            sidebar_html += '<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i> </span></a>';
                            var thirdMenuItems = subLeftMenuItems[j].items;
                            sidebar_html += '<ul class="treeview-menu">';
                            for(var k in thirdMenuItems){
                                sidebar_html += '<li><a id="nav'+thirdMenuItems[k].content.id+'" class="openlink" data-label="'+thirdMenuItems[k].content.cfg_comment+'" data-id="'+thirdMenuItems[k].content.id+'" href="'+thirdMenuItems[k].content.value.url+'"><i class="'+ ( oa_icon_is_empty( thirdMenuItems[k].content.value.icon ) ? "fa  fa-angle-double-right" : thirdMenuItems[k].content.value.icon) +'"></i> '+thirdMenuItems[k].content.cfg_comment+'</a></li>';
                            }
                            sidebar_html += '</ul></li>'
                        }
                    }
                    sidebar_html += '</ul>';
                }
                sidebar_html += '</li>';
            }
            sidebar_html += '</ul>';
            $('.sidebar').html(sidebar_html).tree();
        }
    }
    initOpenAdmMenusEvents();
    return false;
}

function oa_topmenu_change_active(el) {
    $(el).parent().parent().find('li').removeClass("active");
    $(el).parent().addClass("active");
}

function stopPropagation(e) {
    e = e || window.event;
    if(e.stopPropagation) { //W3C阻止冒泡方法
        e.stopPropagation();
    } else {
        e.cancelBubble = true; //IE阻止冒泡方法
    }
}

function oa_top_menu_click() {

    $('#topmenu a').each(function (index,el) {
        $(el).click(function (e) {
            var url = $(el).data('url');
            if(typeof url != "undefined"){
                oa_open_window(el);
            }else{
                oa_build_left_menu(el);
            }
            $('#topmenu li').removeClass('active');
            $(el).parent().addClass('active');
            return false;
        })
    })
}

function oa_task_tab(label,id) {
    var tab_box    = $('#tab_box');
    var tabnav_box = $('#tab_nav');
    if($('#tab_nav_'+id).length==0) {
        //create tab nav
        var tab_nav = $('<li data-id="'+id+'"  id="tab_nav_'+id+'" class="taskactive"><a href="#tab_'+id+'"  data-toggle="task-tab">'+label+' <i class="fa fa-remove" onclick="oa_tab_close('+id+')"></i></a></li>');
        tabnav_box.append(tab_nav);

        //create content
        var tab = $('<div class="tab-pane" id="tab_'+id+'"></div>');
        tab_box.append(tab);

        oa_tab_context_menu(tab_nav);
        oa_setTabActiveById(id);
    }

}

function oa_task_toggle() {
    $('')
}

function oa_open_app(url,label,id) {

    if(oa_check_need_iframe(url)){
        oa_open_iframe(url,label,id);
    }else{
        oa_open_single(url,label,id);
    }
}

function oa_check_need_iframe(url) {
    return new RegExp("iframe").test(url);
}

function oa_open_single(url,label,id) {
    oa_task_tab(label,id);
    var iframe = $('<div id="iframe_'+id+'" data-url="'+ url +'" data-label="'+label+'"></div>');
    $('#tab_'+id).html(iframe);
    $(iframe).load(url);
}

function oa_open_iframe(url,label,id) {
    oa_task_tab(label,id);
    $('#tab_nav_'+id).on('shown.bs.task-tab', function (e) {
        var id = $(e.target).parent().data('id');
        height = oa_intval($('#iframe_' + id).outerHeight());
        oa_tab_iframe_height(id, height);//需要重新设置iframe的高度,否则点击其他tab再点击回来iframe高度不可用。
    });
    var iframe = $('<iframe id="iframe_' + id + '" width="100%" frameborder="no" border="0" marginwidth="0" marginheight="0" scrolling="auto" allowtransparency="yes" src="" />');
    $('#tab_' + id).html(iframe);
    $("#iframe_" + id).attr('src', url);
    oa_tab_iframe_height(id);
}

function oa_open_window(el) {
    var id = $(el).data('id');
    var url   = $(el).attr('href');
    if(url=="#"){
        url = $(el).data('url');
    }
    var label = $(el).data('label');
    if(typeof label == "undefined"){
        label = $(el).text();
    }
    oa_open_app(url,label,id)
    return false;
}

function oa_intval(height) {
    var height = parseInt(height);
    return isNaN(height) ? 0 : height;
}

function oa_tab_iframe_height(id,height) {
    var iframe_min_height = 550;
    var body_height    = oa_intval($(window).outerHeight());
    var header_height  = oa_intval($('.main-header').outerHeight());
    var footer_height  = oa_intval($('.main-footer').outerHeight());
    var tab_nav_height = oa_intval($('#tab_nav').outerHeight());
    //每次都重新设置高度
    var iframe_height  = body_height - header_height - tab_nav_height - footer_height;
    //console.log(body_height ,header_height , tab_nav_height , footer_height)
    if(iframe_height < iframe_min_height){
        iframe_height = iframe_min_height;
    }
    if(typeof height == "number"){
        //需要重新设置iframe的高度,否则点击其他tab再点击回来iframe高度不可用。
        //必须要赋值一个新的高度,否则不生效
        if(iframe_height == height){
            $("#iframe_"+id).attr('height',iframe_height-1);
        }else{
            $("#iframe_"+id).attr('height',iframe_height);
        }
    }else{
        $("#iframe_"+id).attr('height',iframe_height);
    }

    return true;
}

function initOpenAdmMenusEvents() {

    $('.sidebar-menu .openlink').each(function (index,el) {
        $(el).bind('click',function (e) {
            $('.sidebar-menu li').removeClass('active');
            $(el).parent().addClass('active');
            oa_open_window(el);
            return false;
        });//end click
    });
    $('.user-menu .openlink').each(function (index,el) {
        $(el).bind('click',function (e) {
            oa_open_window(el);
            return false;
        });//end click
    });
}

function oa_setTabActiveById(id) {
    //切换active为当前的tab
    $('#tab_nav li').removeClass('taskactive');
    $('#tab_nav_'+id).addClass('taskactive');
    //tab content
    $('#tab_box div').removeClass('taskactive');
    $('#tab_'+id).addClass('taskactive');
}

function resizeIFramesSize() {
    var body_height    = $('body').outerHeight();
    var header_height  = $('.main-header').outerHeight();
    var footer_height  = $('.main-footer').outerHeight();
    var tab_nav_height = $('#tab_nav').outerHeight();
    var iframe_height  = body_height - header_height - tab_nav_height - footer_height;
    $("iframe").attr('height',iframe_height);
}

function oa_tab_close(id) {
    var tabnavbid = '#tab_nav_'+id;
    var tabid    = '#tab_'+id;

    var next = $(tabnavbid).next();
    var prev = $(tabnavbid).prev();
    if(next.length>0){
        oa_setTabActiveById($(next).data('id'));
    }else{
        if(prev.length>0){
            oa_setTabActiveById($(prev).data('id'));
        }
    }

    $(tabid).remove();
    $(tabnavbid).remove();
}

function oa_app_refresh(id) {
    var elid = 'iframe_'+id;
    if($('div#'+elid).length>0){
        //single
        var url = $('div#'+elid).data('url');
        var label = $('div#'+elid).data('label');
        oa_open_single(url,label,id);
    }else{
        //iframe
        var url = oa_timestamp(document.getElementById(elid).contentDocument.location.href);
        document.getElementById('iframe_'+id).contentDocument.location.href = url;
    }
}

function oa_tab_context_menu(el) {
    var id = $(el).data('id');
    $(el).contextMenu('tabmenu',{
        bindings:{
            'refresh':function (t) {
                oa_setTabActiveById(id);
                oa_app_refresh(id);
                $("div#tabmenu").hide();
            },
            'cancel': function(t) {
                $("div#tabmenu").hide();
            },
            'closeSelf':function(t){
                oa_tab_close(id);
            },
            'closeAll':function(t){
                $('#tab_nav').empty();
                $('#tab_box').empty();
            },
            'closeOther':function(t){
                $('#tab_nav li').each(function(i,o){
                    var oid = $(o).data('id');
                    if(oid != id){
                        oa_tab_close(oid);
                    }
                });
            },
            'closeLeft':function(t){
                $('#tab_nav_'+id).prevAll().remove();
                $('#tab_'+id).prevAll().remove();
                oa_setTabActiveById(id);
            },
            'closeRight':function(t){
                $('#tab_nav_'+id).nextAll().remove();
                $('#tab_'+id).nextAll().remove();
                oa_setTabActiveById(id);
            }
        }
    });
}

/**
 * 递归获取content.id
 * @param menus
 * @param depth
 */
function oa_menu_ids(menus,depth) {
    if(depth>=5)return;
    depth = depth || 0;
    for(var i in menus){
        if(typeof menus[i].content == 'object' && typeof menus[i].content.id != 'undefined'){
            OA_MenusIDs.push(parseInt(menus[i].content.id))
        }
        if(typeof menus[i].items == 'object'){
            oa_menu_ids(menus[i].items,depth++)
        }
        if(typeof menus[i] == 'object'){
            oa_menu_ids(menus[i],depth++)
        }
    }
}

function oa_update_menu(delMenuId)
{
    //如果是删除菜单的操作,则需要关闭相应的tab window
    if(typeof delMenuId == "number")oa_tab_close(delMenuId);

    //记录当前的top menu的active状态
    var activeLi = $('#topmenu li.active');
    var activeMenuId = 0;
    if(activeLi.length>0){
        activeMenuId = parseInt($(activeLi).find('a').data('id'));
    }
    //记录左侧菜单的active状态
    var leftActiveLi = $('.sidebar-menu li.active');
    var leftActiveMenuId = 0;
    if(leftActiveLi.length>0){
        leftActiveMenuId = parseInt($(leftActiveLi).find('a').data('id'));
    }
    //请求后台,获取最新的菜单数据
    $.get('/admin/dashboard/index',function (data) {
        $('body').append(data);
        oa_build_top_menu();
        oa_menu_ids(OA_Menus_Children,0);

        //判断打开的tabs的页面 不在 OA_Menus_Children 的要关闭
        $('#tab_nav li').each(function () {
            var id = parseInt($(this).data('id'));
            if(OA_MenusIDs.indexOf(id) === -1){
                oa_tab_close(id);
            }
        })
        var hasFoundOldMenu = false;
        $('#topmenu a').each(function (i,el) {
            if(activeMenuId == $(el).data('id')){
                $(el).click();
                hasFoundOldMenu = true;
            }
        });
        if(!hasFoundOldMenu){
            $("#topmenu").find("li:first a").click();
        }
        if(leftActiveMenuId>0){
            $('#nav'+leftActiveMenuId).trigger("click");
        }
    });
}

function oa_timestamp(url) {
    var timestamp = new Date().getTime();
    var newurl = '';
    if(url.indexOf('?') == -1){
        newurl = url + '?t=' + timestamp;
    }else{
        var reg = new RegExp('t=\d+?&','g');
        newurl  = url.replace(reg,'t='+timestamp+'&');
    }
    return newurl;
}
(function ($) {
    $.getUrlParam = function (name) {
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)");
        var r = window.location.search.substr(1).match(reg);
        if (r != null) return decodeURI(r[2]); return null;
    }
})(jQuery);
function checkTopWindow() {
    var pattern = new RegExp('/admin/dashboard');
    if(!pattern.test(top.location.pathname)){
        //not top
        if(typeof User == "undefined"){
            var pattern = new RegExp("#\d*");
            if(!pattern.test(document.title)){
                var url = window.location.protocol+'//'+window.location.host+'/admin/dashboard?url='+location.href+"&title="+document.title;
                top.location.href = url;
            }
        }
    }else{
        //top
        //check if url!=""
        if(location.search != ""){
            var url = $.getUrlParam("url");
            var label = $.getUrlParam("title");
            if(url != ""){
                oa_open_app(url,label,1000);
            }
        }
    }
}

top.window.onresize = function (e) {
    resizeIFramesSize();
}