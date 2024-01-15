var baseUrl = 'http://the8.com'
layui.use('layer', function () {
    var $ = layui.jquery, 
    layer = layui.layer;
})
function logout() {
    window.location.href = '../../login.html'
    sessionStorage.removeItem('token')
}