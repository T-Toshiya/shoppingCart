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
//        var endPage = $("#userDisp").data('lastpage');
        var end_flag = 0; //最後のページまで行ったら1にして読み込みを終了させる
        var currentMenu = $("#currentMenu div").attr('id');
        if (currentMenu == 'products') {
            var endPage = $(".lastPage").data('lastpage');
        } else {
            var endPage = $(".orderHistoryLastPage").data('lastpage');
        }
        var searchText = $("#searchText").val();
        //var searchContent = $("[name=search]:checked").val();
        console.log(endPage);
        if (currentMenu == 'products') {
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
                    fd.append("searchText", searchText);
                    //fd.append("searchContent", searchContent);

                    setTimeout(function() {
                        $.ajax({
                            type: 'POST',
                            url: '/autoPaging',
                            data: fd,
                            processData: false,
                            contentType: false,
                        }).done(function(data) {
                            console.log(page);
                            $(".loading").html('');
                            if (page <= endPage) {
                                //$("#userDisp").append(data);
                                $("#productList").append(data);
                                obj.data('loading', false);
                            } else {
                                end_flag++;
                                obj.data('loading', false);
                            } 
                        }).fail(function(data) {
                            console.log('fail');
                            alert('通信エラー');
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
            console.log(productsList);
            $("#productList").html(productsList);
            $("#currentMenu").removeAttr('id');
            $("#products").parent('li').attr('id', 'currentMenu');
            $("#searchBtn").val("商品検索");
            $("#searchText").attr('class', 'searchProduct');
            $("#searchText").val("");
            $("#searchContents").show();
            $("#deleteOrderHistory").hide();
            $(window).unbind("bottom");
            init();
            //insertCart();
            //showCart();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    

    //カートの表示
    $(document).on('click', '#cart a', function() {
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
            //$("#userDisp").html(cartList);
            $("#productList").html(cartList);
            $("#currentMenu").removeAttr('id');
            $("#cart").parent('li').attr('id', 'currentMenu');
            $("#searchContents").hide();
            $("#deleteOrderHistory").hide();
            $(window).unbind("bottom");
            init();
            orderConfirm();
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
            //$("#productList").html(orderHistory);
            $("#currentMenu").removeAttr('id');
            $("#orderHistory").parent('li').attr('id', 'currentMenu');
            $("#searchBtn").val("注文検索");
            $("#searchText").attr('class', 'searchOrder');
            $("#searchText").val("");
            $("#searchContents").show();
            $("#deleteOrderHistory").show();
            //$(window).unbind("bottom");
            init();
            //insertCart();
            //showCart();
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
        var searchContent = $("#searchText").attr('class');
        var fd = new FormData();
        fd.append("searchText", searchText);
        fd.append("searchContent", searchContent);
        $.ajax({
            type: 'POST',
            url: '/search',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            console.log(productsList);
            //$("#userDisp").html(productsList);
            $("#productList").html(productsList);
            
            //自動読み込みしないようにする
            $(window).unbind("bottom");
            init();
        }).fail(function(error) {
            alert('通信エラー');
        });
    });
    
    $(document).on('click', '.insertCartBtn', function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var productId = $(this).prev("input").val();
        var selectedNum = $("#productNum_" + productId).val();
        var fd = new FormData();
        fd.append("productId", productId);
        fd.append("selectedNum", selectedNum);
        $.ajax({
            type: 'POST',
            url: '/insertCart',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(totalNum) {
            $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
            init();
            //showCart();
        }).fail(function(error) {
            console.log(error);
            alert('不正アクセスエラー');
        });
    });
    
    $(document).on('click', '.insertAmazonCartBtn', function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var productId = $(this).prev("input").val();
        var productName = $("#productName_"+productId).html();
        var productPrice = $("#productPrice_"+productId).html().substr(1).split(',').join('');
        console.log(productPrice);
        var imagePath = $("#productImage_"+productId+ " img").attr('src');
        var selectedNum = $("#productNum_" + productId).val();
        var fd = new FormData();
        fd.append("productId", productId);
        fd.append("productName", productName);
        fd.append("productPrice", productPrice);
        fd.append("imagePath", imagePath);
        fd.append("selectedNum", selectedNum);
        $.ajax({
            type: 'POST',
            url: '/insertAmazonCart',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(totalNum) {
            $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
            init();
            //showCart();
        }).fail(function(error) {
            console.log(error);
            alert('不正アクセスエラー');
        });
    });
    
    function orderConfirm() {
        $("#orderConfirm").click(function() {
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
                    //$("#userDisp").html(productsList);
                    $("#productList").html(productsList);
                    $("#currentMenu").removeAttr('id');
                    $("#products").parent('li').attr('id', 'currentMenu');
                    init();
                    //insertCart();
                    //showCart();
                }).fail(function(error) {
                    alert('不正アクセスエラー');
                });
            }
        });
    }
    
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
        }).done(function(result) {
            var totalNum = result[0];
            var totalPrice = result[1];
            
            $("#cart_"+id).remove();
            if (totalNum == 0) {
                $("#cart").html("カート("+totalNum+"点)");
            } else {
                $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
            }
            $("#total").html("小計("+totalNum+"点):¥"+totalPrice);
        }).fail(function(error) {
            console.log(error);
            $("#userDisp").html(error);
            alert('不正アクセスエラー');
        });
    }
}

function changeCart(id, productId) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var selectedId = productId;
    var selectedNum = $("#cartProductNum_"+id+" option:selected").val();
    
    var fd = new FormData();
    fd.append("selectedId", selectedId);
    fd.append("selectedNum", selectedNum);
    $.ajax({
        type: 'POST',
        url: '/changeCart',
        data: fd,
        processData: false,
        contentType: false,
    }).done(function(result) {
        var postPrice = result[0];
        var totalNum = result[1];
        var totalPrice = result[2];
        
        $("#price_"+id).html("¥"+postPrice);
        $("#total").html("小計("+totalNum+"点):¥"+totalPrice);
        $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
    }).fail(function(error) {
        console.log(error);
        //alert('不正アクセスエラー');
    });
}

function deleteOrderHistory() {
    if (confirm('購入履歴を削除しますか。')) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var fd = new FormData();
        $.ajax({
            type: 'POST',
            url: '/deleteOrderHistory',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(result) {
            $("#productList").html(result);
            //$("#userDisp").html(result);
        }).fail(function(error) {
            console.log(error);
            //alert('不正アクセスエラー');
        });
    }
}