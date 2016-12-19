$(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    init();
    
    function init() {
        //スクロールで自動読み込み
        //初期設定をクリア
        $(window).unbind("bottom");

        var page = 1; //ページ番号
        var end_flag = 0; //最後のページまで行ったら1にして読み込みを終了させる
        var currentMenu = $("#currentMenu div").attr('id');
        console.log(currentMenu);
        
        currentMenu === 'products' ? endPage = 10 : endPage = $(".orderHistoryLastPage").data('lastpage');

        //var searchText = $("#searchText").val();
        if (currentMenu === 'products' || currentMenu === 'orderHistory') {
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
                    fd.append("searchText", $("#searchText").val());

                    setTimeout(function() {
                        $.ajax({
                            type: 'POST',
                            url: '/autoPaging',
                            data: fd,
                            processData: false,
                            contentType: false,
                        }).done(function(data) {
                            $(".loading").html('');
                            if (page <= endPage) {
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
        $.ajax({
            type: 'POST',
            url: '/showProducts',
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            $("#productList").html(productsList);
            $("#currentMenu").removeAttr('id');
            $("#products").parent('li').attr('id', 'currentMenu');
            $("#searchBtn").val("商品検索");
            $("#searchText").attr('class', 'searchProduct');
            $("#searchText").val("");
            $("#searchContents").show();
            $("#deleteOrderHistory").hide();
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    

    //カートの表示
    $(document).on('click', '#cart a', function() {
        $.ajax({
            type: 'POST',
            url: '/showCart',
            processData: false,
            contentType: false,
        }).done(function(cartList) {
            $("#productList").html(cartList);
            $("#currentMenu").removeAttr('id');
            $("#cart").parent('li').attr('id', 'currentMenu');
            $("#searchContents").hide();
            $("#deleteOrderHistory").hide();
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    
    
    //購入履歴の表示
    $("#orderHistory").click(function() {
        $.ajax({
            type: 'POST',
            url: '/showOrderHistory',
            processData: false,
            contentType: false,
        }).done(function(orderHistory) {
            $("#userDisp").html(orderHistory);
            $("#currentMenu").removeAttr('id');
            $("#orderHistory").parent('li').attr('id', 'currentMenu');
            $("#searchBtn").val("注文検索");
            $("#searchText").attr('class', 'searchOrder');
            $("#searchText").val("");
            $("#searchContents").show();
            $("#deleteOrderHistory").show();
            init();
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    });
    
    //商品検索
    $("#searchBtn").click(function() {
//        var searchText = $("#searchText").val();
//        var searchContent = $("#searchText").attr('class');
//        var fd = new FormData();
//        fd.append("searchText", searchText);
//        fd.append("searchContent", searchContent);
        var fd = new FormData();
        fd.append("searchText", $("#searchText").val());
        fd.append("searchContent", $("#searchText").attr('class'));
        $.ajax({
            type: 'POST',
            url: '/search',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(productsList) {
            $("#productList").html(productsList);
            init();
        }).fail(function(error) {
            alert('通信エラー');
        });
    });
    
    $(document).on('click', '.insertCartBtn', function() {
        var fd = new FormData();
        fd.append("productId", $(this).prev("input").val());
        fd.append("selectedNum", $("#productNum_" + productId).val());
        $.ajax({
            type: 'POST',
            url: '/insertCart',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(totalNum) {
            $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
            init();
        }).fail(function(error) {
            console.log(error);
            alert('不正アクセスエラー');
        });
    });
    
    $(document).on('click', '.insertAmazonCartBtn', function() {
        var productId = $(this).prev("input").val();
        var fd = new FormData();
        fd.append("productId", productId);
        fd.append("productName", $("#productName_"+productId).html());
        fd.append("productPrice", $("#productPrice_"+productId).html().substr(1).split(',').join(''));
        fd.append("imagePath", $("#productImage_"+productId+ " img").attr('src'));
        fd.append("selectedNum", $("#productNum_" + productId).val());
        $.ajax({
            type: 'POST',
            url: '/insertAmazonCart',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(totalNum) {
            $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
            init();
        }).fail(function(error) {
            console.log(error);
            alert('不正アクセスエラー');
        });
    });
    
    $(document).on('click', '#orderConfirm', function() {
        if (confirm('注文を確定しますか')) {
            $.ajax({
                type: 'POST',
                url: '/confirm',
                processData: false,
                contentType: false,
            }).done(function(productsList) {
                alert('注文が確定しました');
                $("#cart").html("カート(0点)");
                $("#productList").html(productsList);
                $("#currentMenu").removeAttr('id');
                $("#products").parent('li').attr('id', 'currentMenu');
                init();
            }).fail(function(error) {
                alert('不正アクセスエラー');
            });
        }
    });
    
    $(document).on('click', '#deleteItem', function() {
        if (confirm('削除してよろしいですか')) {
            var fd = new FormData();
            deleteId = $("#deleteItem").data("deleteid");
            fd.append("deleteId", deleteId);
            $.ajax({
                type: 'POST',
                url: '/delete',
                data: fd,
                processData: false,
                contentType: false,
            }).done(function(result) {
                var totalNum = result[0];
                var totalPrice = result[1];

                $("#cart_"+deleteId).remove();

                if (totalNum == 0) {
                    $("#cart").html("カート("+totalNum+"点)");
                } else {
                    $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
                }
                $("#total").html("小計("+totalNum+"点):¥"+totalPrice);
            }).fail(function(error) {
                $("#userDisp").html(error);
                alert('不正アクセスエラー');
            });
        }
    });
    
    $(document).on('change', '.cartProductNum', function() {
        var productId = $(this).data("productid");
        var nowPriceText = $("#total").text().split('¥');
        var nowPrice = nowPriceText[1].split(',').join('');
        var fd = new FormData();
        fd.append("selectedId", productId);
        fd.append("selectedNum", $("#cartProductNum_"+productId+" option:selected").val());
        fd.append("nowPrice", nowPrice);
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

            $("#price_"+productId).html("¥"+postPrice);
            $("#total").html("小計("+totalNum+"点):¥"+totalPrice);
            $("#cart").html("<a href='javascript:void(0)'>カート("+totalNum+"点)</a>");
        }).fail(function(error) {
            console.log(error);
            //alert('不正アクセスエラー');
        });
    });
    
    $(document).on('click', '#deleteOrderHistory', function() {
        if (confirm('購入履歴を削除しますか。')) {
            $.ajax({
                type: 'POST',
                url: '/deleteOrderHistory',
                processData: false,
                contentType: false,
            }).done(function(result) {
                $("#productList").html("No products yet");
            }).fail(function(error) {
                console.log(error);
                //alert('不正アクセスエラー');
            });
        }
    });
    
    //一番上に戻る
    var pageTop = $("#pageTop");
    pageTop.hide();

    $(window).scroll(function() {
        //三項演算子にした
        $(this).scrollTop() > 100 ? pageTop.fadeIn() : pageTop.fadeOut();
        
//        if ($(this).scrollTop() > 100) {
//            pageTop.fadeIn();
//        } else {
//            pageTop.fadeOut();
//        }
    });

    pageTop.click(function() {
        $("body, html").animate({
            scrollTop: 0
        }, 500);
        return false;
    });
});