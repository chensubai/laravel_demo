function login() {
    let params = {
        username: null,
        password: null,
        captcha: null,
        key: null
    }
    params.username = $('#username').val()
    params.password = $('#password').val()
    params.captcha = $('#captcha').val()
    params.key = sessionStorage.getItem('key')
    $.post(baseUrl + '/api/login', params, function (data) {
        if (data.code == 200) {
            sessionStorage.setItem('token', data.data.token.token_type + ' ' + data.data.token.token)
            let user = JSON.stringify(data.data.user)
            sessionStorage.setItem('user', user)
            window.location.href = '../cms/index.html'
        } else {
            getagain()
        }
    })
}
function register() {
    let params = {
        username: null,
        nikename: null,
        email: null,
        mobile: null,
        password: null,
        captcha: null,
        key: null
    }
    params.username = $('#username').val()
    params.nikename = $('#nikename').val()
    params.email = $('#email').val()
    params.mobile = $('#mobile').val()
    params.password = $('#password').val()
    params.captcha = $('#captcha').val()
    params.key = sessionStorage.getItem('key')
    $.post(baseUrl + '/api/register', params, function (data) {
        if (data.code == 200) {
            sessionStorage.setItem('token', data.data.token.token_type + ' ' + data.data.token.token)
            window.location.href = './login.html'
        } else {
            layer.msg(data.msgs[0].code);
            getagain()
        }
    })
}
function getagain() {
    $.get(baseUrl + "/api/codeImg", {}, function (data) {
        $('#verify').attr('src', data.data.code.img)
        let key = data.data.code.key
        sessionStorage.setItem('key', key)
    }, "json")
}

layui.use('form', function () {
    var form = layui.form;
    // 表单验证
    form.verify({
        jp_mobile: function (value, item) {
            if (!new RegExp("^(070|080|090)[0-9]{8}$").test(value)) {
                return '正しい携帯電話番号を入力ください!';
            }
        },
        email: function (value, item) {
            if (!new RegExp().test(value)) {
                return ''
            }
        }
    });
});
getagain()