# Окно авторизации на текущей странице вместо popup окна

```javascript
var clientId = "<?= TR_ID_OPT_CLIENT_ID  ?>";
var redirectUri = "<?= TR_ID_URI_HOST ?>" + "/bitrix/components/trusted/id/authorize.php";
var scope = "userprofile";

var url = "https://id.trusted.plus/idp/sso/oauth";
url += "?client_id=" + clientId + "&redirect_uri=" + encodeURIComponent(redirectUri);
url += "&scope=" + scope + "&state="+encodeURIComponent(window.location.href)+"&final=true";
window.location = url;
```

