$(function() {

    init();

    function init() {
        //スクロールで自動読み込み
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //初期設定をクリア
        $(window).unbind("bottom");

        var page = 1; //ページ番号
        var endPage = $("#userDisp").data('lastpage');
        var end_flag = 0; //最後のページまで行ったら1にして読み込みを終了させる
        var currentMenu = $("#currentMenu a").attr('id');
        //var searchText = $("#searchText").val();
        //var searchContent = $("[name=search]:checked").val();
        if (currentMenu !== 'cart') {
        $(window).bottom({proximity: 0.05});
        $(window).bind("bottom", function() {
            if (end_flag == 0) {
                var obj = $(this);
                if (! obj.data("loading")) {
                    obj.data("loading", true);

                    $(".loading").html('loading...');

                    var fd = new FormData();
                    page++;
                    fd.append("currentPage", page);
                    fd.append("currentMenu", currentMenu);
                    //fd.append("searchText", searchText);
                    //fd.append("searchContent", searchContent);

                    setTimeout(function() {
                        $.ajax({
                            type: 'POST',
                            url: '/autoPaging',
                            data: fd,
                            processData: false,
                            contentType: false,
                        }).done(function(data) {
                            if (page <= endPage) {
                                $("#userDisp").append(data);
                                obj.data('loading', false);
                                console.log(currentMenu);
                            } else {
                                end_flag++;
                                $(".loading").html('');
                                obj.data('loading', false);
                            } 
                        }).fail(function(data) {
                            console.log('fail');
                        });
                    })
                }
            }
        });
        }
    }
    
    //商品一覧の表示
    $("#products").click(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        $.ajax({
            type: 'POST',
            url: '/showProducts',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            $("#userDisp").html(productsList);
            $("#currentMenu").removeAttr('id');
            $("#products").parent('li').attr('id', 'currentMenu');
            $(window).unbind("bottom");
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    
    //カートの表示
    $("#cart").click(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        $.ajax({
            type: 'POST',
            url: '/showCart',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(cartList) {
            $("#userDisp").html(cartList);
            $("#currentMenu").removeAttr('id');
            $("#cart").parent('li').attr('id', 'currentMenu');
            $(window).unbind("bottom");
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    
    //購入履歴の表示
    $("#orderHistory").click(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        $.ajax({
            type: 'POST',
            url: '/showOrderHistory',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(orderHistory) {
            $("#userDisp").html(orderHistory);
            $("#currentMenu").removeAttr('id');
            $("#orderHistory").parent('li').attr('id', 'currentMenu');
            //$(window).unbind("bottom");
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    
    //商品検索
    $("#searchBtn").click(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var searchText = $("#searchText").val();
        console.log(searchText);
        var fd = new FormData();
        fd.append("searchText", searchText);
        $.ajax({
            type: 'POST',
            url: '/search',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            $("#userDisp").html(productsList);
            $("#currentMenu").removeAttr('id');
            $("#products").parent('li').attr('id', 'currentMenu');

            //自動読み込みしないようにする
            $(window).unbind("bottom");
            init();
        });
    });
    
    //一番上に戻る
    var pageTop = $("#pageTop");
    pageTop.hide();

    $(window).scroll(function() {
        if ($(this).scrollTop() > 100) {
            pageTop.fadeIn();
        } else {
            pageTop.fadeOut();
        }
    });

    pageTop.click(function() {
        $("body, html").animate({
            scrollTop: 0
        }, 500);
        return false;
    });
});



function insertCart(id) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    var productId = id;
    var selectedNum = $("#productNum_" + id).val();
    var fd = new FormData();
    fd.append("productId", id);
    fd.append("selectedNum", selectedNum);
    $.ajax({
        type: 'POST',
        url: '/insertCart',
        data: fd,
        processData: false,
        contentType: false,
    }).done(function(totalNum) {
        $("#cart").html("カート("+totalNum+"点)")
    }).fail(function(error) {
        console.log(error);
        alert('不正アクセスエラー');
    });
}

function destroy(id) {
    if (confirm('削除してよろしいですか')) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var deleteId = id;
        var fd = new FormData();
        fd.append("deleteId", id);
        $.ajax({
            type: 'POST',
            url: '/delete',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(cartList) {
            //$("#cart_"+id).remove();
            //$("#cart").html("カート("+totalNum+"点)")
            $("#userDisp").html(cartList);
        }).fail(function(error) {
            console.log(error);
            $("#userDisp").html(error);
            alert('不正アクセスエラー');
        });
    }
}

function orderConfirm() {
    if (confirm('注文を確定しますか')) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        $.ajax({
            type: 'POST',
            url: '/confirm',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            alert('注文が確定しました');
            $("#cart").html("カート(0点)");
            $("#userDisp").html(productsList);
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    }
}