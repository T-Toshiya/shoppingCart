function insertCart(id, userName) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    var productId = id;
    var selectedNum = $("#productNum_" + id).val();
    var userName = userName;
    var fd = new FormData();
    fd.append("productId", id);
    fd.append("selectedNum", selectedNum);
    fd.append("userName", userName);
    $.ajax({
        type: 'POST',
        url: '/insertCart',
        data: fd,
        processData: false,
        contentType: false,
    }).done(function(totalNum) {
        $("#cart").html("カート("+totalNum+"点)")
    }).fail(function(error) {
        alert('不正アクセスエラー');
    });
}

function destroy(id, userName) {
    if (confirm('削除してよろしいですか')) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var deleteId = id;
        var userName = userName;
        var fd = new FormData();
        fd.append("deleteId", id);
        fd.append("userName", userName);
        $.ajax({
            type: 'POST',
            url: '/delete',
            data: fd,
            processData: false,
            contentType: false,
        }).done(function(totalNum) {
            //$("#cart_"+id).remove();
            //$("#cart").html("カート("+totalNum+"点)")
            console.log(totalNum);
        }).fail(function(error) {
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
        }).done(function(totalNum) {
            //$("#cart_"+id).remove();
            //$("#cart").html("カート("+totalNum+"点)")
            alert('注文が確定しました');
            console.log(totalNum);
        }).fail(function(error) {
            alert('不正アクセスエラー');
        });
    }
}