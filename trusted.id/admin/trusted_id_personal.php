<?

use Trusted\Id;
use Bitrix\Main\Config\Option;

// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/subscribe/include.php"); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/subscribe/prolog.php"); // пролог модуля

CModule::IncludeModule('trusted.id');
// подключим языковой файл
IncludeModuleLangFile(__FILE__);

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("subscribe");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$personalID = intval($_REQUEST["id"]);
// сформируем список закладок
$aTabs = array(
    array("DIV" => "edit1", "TAB" => 'Пользователь', "ICON" => "main_user_edit", "TITLE" => GetMessage('TR_ID_PERSONAL_PAGE_TITLE')),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);        // идентификатор редактируемой записи
$message = null;        // сообщение об ошибке
$bVarsFromForm = false; // флаг "Данные получены с формы", обозначающий, что выводимые данные получены с формы, а не из БД.

$rsUser = CUser::GetByID($personalID);
$arUser = $rsUser->Fetch();

$token = Id\OAuth2::getFromSession();
if ($token) {
    $token = $token->getAccessToken();
    $bxUser = CUser::GetById($ID);
    $bxUser = $bxUser->Fetch();
    $tnUserInfo = Id\TAuthCommand::pullTnInfo($token, 'email', $bxUser['EMAIL']);
    if ($tnUserInfo) {
        Id\TDataBaseUser::removeUserByUserId($ID);
        $serviceUser = Id\ServiceUser::fromArray($tnUserInfo);
        $user = new Id\TUser();
        $user->setServiceUser($serviceUser);
        $user->setUserId($ID);
        $user->save();
    }
}

if ($personalID >= 1) {

    $UID_TR = Id\TDataBaseUser::getUserByUserId($personalID);

    $personalData = Array(
        "BX_ID" => $UID_TR->getUserId(),
        "BX_LOGIN" => $arUser['LOGIN'],
        "BX_NAME" => $arUser['NAME'],
        "BX_LAST_NAME" => $arUser['LAST_NAME'],
        "BX_EMAIL" => $arUser['EMAIL'],
        "TR_UID" => $UID_TR->getId(),
        "TN_GIV_NAME" => $UID_TR->getGivenName(),
        "TN_FAM_NAME" => $UID_TR->getFamilyName(),
        "TN_EMAIL" => $UID_TR->getEmail(),
    );
}

if (
    $REQUEST_METHOD == "POST" // проверка метода вызова страницы
    &&
    ($save != "" || $apply != "") // проверка нажатия кнопок "Сохранить" и "Применить"
    &&
    $POST_RIGHT == "W"          // проверка наличия прав на запись для модуля
    &&
    check_bitrix_sessid()     // проверка идентификатора сессии
) {
//сохранение изменений

}

$APPLICATION->SetTitle(GetMessage('TR_ID_PERSONAL_PAGE_TITLE'));

// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

// конфигурация административного меню
$aMenu = array(
    array(
        "TEXT" => GetMessage('TR_ID_PERSONAL_BTN_BACK'),
        "TITLE" => GetMessage('TR_ID_PERSONAL_BTN_BACK'),
        "LINK" => 'trusted_id_users.php?lang=' . $lang,
        "ICON" => "btn_list",
    )
);

if ($ID > 0) {//полностью переписать элементы aMenu ниже
    $aMenu[] = array("SEPARATOR" => "Y");
    $aMenu[] = array(
        "TEXT" => GetMessage("rub_add"),
        "TITLE" => GetMessage("rubric_mnu_add"),
        "LINK" => 'trusted_id_personal.php?id=' . $ID . '&lang=' . $lang,
        "ICON" => "btn_new",
    );
    $aMenu[] = array(
        "TEXT" => GetMessage("rub_delete"),
        "TITLE" => GetMessage("rubric_mnu_del"),
        "LINK" => 'trusted_id_personal.php?id=' . $ID . '&lang=' . $lang,
        "ICON" => "btn_delete",
    );
    $aMenu[] = array("SEPARATOR" => "Y");
    $aMenu[] = array(
        "TEXT" => GetMessage("rub_check"),
        "TITLE" => GetMessage("rubric_mnu_check"),
        "LINK" => 'trusted_id_personal.php?id=' . $ID . '&lang=' . $lang,
    );
}

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!Id\Utils::isSecure()) {
    echo BeginNote(), GetMessage('TR_ID_HTTP_WARNING'), EndNote();
}

if ($_REQUEST["mess"] == "ok" && $ID > 0)
    CAdminMessage::ShowMessage(array("MESSAGE" => GetMessage("rub_saved"), "TYPE" => "OK"));

if ($message)
    echo $message->Show();
elseif ($rubric->LAST_ERROR != "")
    CAdminMessage::ShowMessage($rubric->LAST_ERROR);


if (!Id\OAuth2::getFromSession()) {
    $APPLICATION->IncludeComponent('trusted:id', '');
} else {
    $auth = Id\OAuth2::getFromSession();
    ?>

    <form method="POST" Action="<? echo $APPLICATION->GetCurPage() ?>" ENCTYPE="multipart/form-data"
          name="post_form">
        <? echo bitrix_sessid_post(); ?>
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();
        ?>
        <tr class="heading">
            <td colspan="2"><?= GetMessage("TR_ID_PERSONAL_DATA_IN_BITRIX") ?></td>
        </tr>
        <tr>
            <td width="50%"><?= GetMessage("TR_ID_PERSONAL_COL_ID") ?></td>
            <td width="50%"><?= $personalData['BX_ID'] ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_LOGIN") ?></td>
            <td><?= $personalData['BX_LOGIN'] ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_GIV_NAME") ?></td>
            <td><?= $personalData['BX_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_FAM_NAME") ?></td>
            <td><?= $personalData['BX_LAST_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_EMAIL") ?></td>
            <td><?= $personalData['BX_EMAIL']; ?></td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center">
            <span style="color: #2675d7; cursor: pointer; text-decoration: underline; text-decoration-color: #2675d7"
                  onclick="window.location.href = 'user_edit.php?lang=<?= $lang ?>&ID=<? echo $personalData['BX_ID'] ?>'">
                <?= GetMessage("TR_ID_PERSONAL_EDIT_DATA_BITRIX") ?>
            </span>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?= GetMessage("TR_ID_PERSONAL_DATA_IN_TR") ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_ID") ?></td>
            <td><?= $personalData['TR_UID']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_GIV_NAME") ?></td>
            <td><?= $personalData['TN_GIV_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_FAM_NAME") ?></td>
            <td><?= $personalData['TN_FAM_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_EMAIL") ?></td>
            <td><?= $personalData['TN_EMAIL']; ?></td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?= GetMessage("TR_ID_PERSONAL_FIND_DATA_BY_IND") ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_IND") ?></td>
            <td>
                <? $recoveryWay = array(
                    "REFERENCE" => // массив заголовков элементов
                        array(GetMessage("TR_ID_PERSONAL_SELECTOR_UID"), GetMessage("TR_ID_PERSONAL_SELECTOR_EMAIL"), GetMessage("TR_ID_PERSONAL_SELECTOR_PHONE")),
                    "REFERENCE_ID" => // массив значений элементов
                        array("uid", "email", "phone")
                );
                echo SelectBoxFromArray('recovery_way', $recoveryWay, $arFilter, "", "", true, ""); ?>
            </td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_FIND_DATA_VALUE") ?></td>
            <td><input type="text" name="NAME" size="30" maxlength="100"></td>
        </tr>
        <tr>
            <td><? if (!($personalData['TR_UID'] === null)) { ?>
                    <div style="display: flex; justify-content: flex-end;">
                        <div id="button__onPage" onclick="deleteTrData()">
                            <?= GetMessage("TR_ID_PERSONAL_BTN_DELETE_TR_DATA") ?>
                        </div>
                    </div>
                <? } ?>
            <td>
                <div style="display: flex; justify-content: flex-start;">
                    <div id="button__onPage" onclick="searchTrData()">
                        <div><?= GetMessage("TR_ID_PERSONAL_BTN_FIND_DATA_BY_IND") ?></div>
                    </div>
                </div>
            </td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_ID") ?></td>
            <td id="personalData__UID"></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_GIV_NAME") ?></td>
            <td id="personalData__givName"></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_FAM_NAME") ?></td>
            <td id="personalData__famName"></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_EMAIL") ?></td>
            <td id="personalData__email"></td>
        </tr>
    </form>

    <script>
        function searchTrData() {
            //функция по поиску данных
            //заглушка
            document.getElementById("personalData__UID").innerHTML = "<?= $personalData['TR_UID'] ?>";
            document.getElementById("personalData__givName").innerHTML = "<?= $personalData['TN_GIV_NAME'] ?>";
            document.getElementById("personalData__famName").innerHTML = "<?= $personalData['TN_FAM_NAME'] ?>";
            document.getElementById("personalData__email").innerHTML = "<?= $personalData['TN_EMAIL'] ?>";
        }

        function deleteTrData() {
            //функция для удаления связки
            //заглушка
            document.getElementById("personalData__UID").innerHTML = null;
            document.getElementById("personalData__givName").innerHTML = null;
            document.getElementById("personalData__famName").innerHTML = null;
            document.getElementById("personalData__email").innerHTML = null;
        }
    </script>

    <style>
        #button__onPage:active {
            -webkit-border-radius: 4px;
            border-radius: 4px;
            background-color: #b7c4c9 !important;
            -webkit-box-shadow: inset 0 1px 1px 1px rgba(103, 109, 123, .78);
            box-shadow: inset 0 1px 1px 1px rgba(103, 109, 123, .78);
            background-image: -webkit-linear-gradient(top, rgba(179, 194, 200, .96), rgba(202, 215, 219, .96)) !important;
            background-image: -moz-linear-gradient(top, rgba(179, 194, 200, .96), rgba(202, 215, 219, .96)) !important;
            background-image: -ms-linear-gradient(top, rgba(179, 194, 200, .96), rgba(202, 215, 219, .96)) !important;
            background-image: -o-linear-gradient(top, rgba(179, 194, 200, .96), rgba(202, 215, 219, .96)) !important;
            background-image: linear-gradient(top, rgba(179, 194, 200, .96), rgba(202, 215, 219, .96)) !important;
            border-top: transparent;
            height: 25px;
            outline: none;
            padding: 1px 13px 3px;
        }

        #button__onPage {
            display: flex;
            align-items: center;
            justify-content: center;
            /*width: 125px;*/
            height: 25px;
            webkit-border-radius: 4px;
            padding: 1px 13px 3px;
            vertical-align: middle;
            border-radius: 4px;
            border: none;
            /* border-top: 1px solid #fff; */
            -webkit-box-shadow: 0 0 1px rgba(0, 0, 0, .11), 0 1px 1px rgba(0, 0, 0, .3), inset 0 1px #fff, inset 0 0 1px rgba(255, 255, 255, .5);
            box-shadow: 0 0 1px rgba(0, 0, 0, .3), 0 1px 1px rgba(0, 0, 0, .3), inset 0 1px 0 #fff, inset 0 0 1px rgba(255, 255, 255, .5);
            background-color: #e0e9ec;
            background-image: -webkit-linear-gradient(bottom, #d7e3e7, #fff) !important;
            background-image: -moz-linear-gradient(bottom, #d7e3e7, #fff) !important;
            background-image: -ms-linear-gradient(bottom, #d7e3e7, #fff) !important;
            background-image: -o-linear-gradient(bottom, #d7e3e7, #fff) !important;
            background-image: linear-gradient(bottom, #d7e3e7, #fff) !important;
            color: #3f4b54;
            cursor: pointer;
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            font-weight: bold;
            font-size: 13px;
            /* line-height: 18px; */
            text-shadow: 0 1px rgba(255, 255, 255, 0.7);
            text-decoration: none;
            position: relative;
            vertical-align: middle;
            -webkit-font-smoothing: antialiased;
        }
    </style>

    <?
    $arTemplates = CPostingTemplate::GetList();
// завершение формы - вывод кнопок сохранения изменений
    $tabControl->Buttons(
        array(
            "disabled" => false,
            "back_url" => "trusted_id_users.php?lang=" . LANG,
        )
    );

    $tabControl->End();

    $tabControl->ShowWarnings("post_form", $message);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>