<?

use Trusted\Id;
use Bitrix\Main\Config\Option;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

CModule::IncludeModule('trusted.id');
IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage('TR_ID_PERSONAL_PAGE_TITLE'));

$bxId = (int)$_REQUEST["id"];

if ($REQUEST_METHOD == "POST" && ($save != "" || $apply != "") && check_bitrix_sessid()) {
    $trId = (int)$_POST['trId'];
    $tnUserInfo = Id\TAuthCommand::findTnUserDataById($trId);
    if ($tnUserInfo) {
        Id\TDataBaseUser::removeUserByUserId($bxId);
        $serviceUser = Id\ServiceUser::fromArray($tnUserInfo);
        $user = new Id\TUser();
        $user->setServiceUser($serviceUser);
        $user->setUserId($bxId);
        $user->save();
    }
    if ($_POST["save"] != '') {
        LocalRedirect(BX_ROOT . "/admin/trusted_id_users.php?lang=" . LANG);
    } else {
        LocalRedirect(BX_ROOT . "/admin/trusted_id_user_edit.php?id=" . urlencode($_POST["id"]) . "&lang=" . LANG);
    }
}

$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => GetMessage('TR_ID_PERSONAL_TAB_TITLE'),
        "ICON" => "main_user_edit",
        "TITLE" => GetMessage('TR_ID_PERSONAL_TAB_TITLE'),
    ),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$bxUser = CUser::GetById($bxId);
$bxUser = $bxUser->Fetch();

if ($bxId >= 1) {
    $trUserInfo = Id\TDataBaseUser::getUserByUserId($bxId);
    $personalData = array(
        "BX_ID" => $bxId,
        "BX_LOGIN" => $bxUser['LOGIN'],
        "BX_NAME" => $bxUser['NAME'],
        "BX_LAST_NAME" => $bxUser['LAST_NAME'],
        "BX_EMAIL" => $bxUser['EMAIL'],
        "TR_UID" => ($trUserInfo === null) ? null : $trUserInfo->getId(),
        "TN_GIV_NAME" => ($trUserInfo === null) ? null : $trUserInfo->getGivenName(),
        "TN_FAM_NAME" => ($trUserInfo === null) ? null : $trUserInfo->getFamilyName(),
        "TN_EMAIL" => ($trUserInfo === null) ? null : $trUserInfo->getEmail(),
    );
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
    array(
        "TEXT" => GetMessage('TR_ID_PERSONAL_BTN_BACK'),
        "TITLE" => GetMessage('TR_ID_PERSONAL_BTN_BACK'),
        "LINK" => 'trusted_id_users.php?lang=' . LANG,
        "ICON" => "btn_list",
    )
);

$context = new CAdminContextMenu($aMenu);

$context->Show();

if (!Id\Utils::isSecure()) {
    echo BeginNote(), GetMessage('TR_ID_HTTP_WARNING'), EndNote();
}
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
                <a href="user_edit.php?lang=<?= LANG ?>&ID=<?= $personalData['BX_ID'] ?>">
                    <?= GetMessage("TR_ID_PERSONAL_EDIT_DATA_BITRIX") ?>
                </a>
            </td>
        </tr>
        <tr class="heading">
            <td colspan="2"><?= GetMessage("TR_ID_PERSONAL_DATA_IN_TR") ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_ID") ?></td>
            <td id="personalTnUID"><?= $personalData['TR_UID']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_GIV_NAME") ?></td>
            <td id="personalTnGivName"><?= $personalData['TN_GIV_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_FAM_NAME") ?></td>
            <td id="personalTnFamName"><?= $personalData['TN_FAM_NAME']; ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_EMAIL") ?></td>
            <td id="personalTnEmail"><?= $personalData['TN_EMAIL']; ?></td>
        </tr>

        <? if (!($personalData['TR_UID'] === null)) { ?>
            <tr id="btnRmvBind">
                <td colspan="2">
                    <div style="display: flex; justify-content: center;">
                        <input type="button"
                               class="adm-workarea adm-btn"
                               onclick="removeUserBinding()"
                               value="<?= GetMessage("TR_ID_PERSONAL_BTN_DELETE_TR_DATA") ?>"/>
                </td>
            </tr>
        <? } ?>

        <tr class="heading">
            <td colspan="2"><?= GetMessage("TR_ID_PERSONAL_FIND_DATA_BY_IND") ?></td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_IND") ?></td>
            <td>
                <?
                $searchType = array(
                    "REFERENCE" => array(
                        GetMessage("TR_ID_PERSONAL_SELECTOR_UID"),
                        GetMessage("TR_ID_PERSONAL_SELECTOR_EMAIL"),
                        GetMessage("TR_ID_PERSONAL_SELECTOR_PHONE"),
                    ),
                    "REFERENCE_ID" => array(
                        "uid",
                        "email",
                        "phone",
                    ),
                );
                echo SelectBoxFromArray("", $searchType, "", "", "id=\"SelectBoxValue\"", false, "post_form");
                ?>
            </td>
        </tr>
        <tr>
            <td><?= GetMessage("TR_ID_PERSONAL_FIND_DATA_VALUE") ?></td>
            <td><input id="findUserValue" type="text" size="30" maxlength="100"></td>
        </tr>
        <tr>
            <td colspan="2">
                <div style="display: flex; justify-content: center;">
                    <input type="button"
                           class="adm-workarea adm-btn"
                           onclick="findValue()"
                           value="<?= GetMessage("TR_ID_PERSONAL_BTN_FIND_DATA_BY_IND") ?>"/>
                </div>
            </td>
        </tr>
        <tr class="search-field">
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_ID") ?></td>
            <td class="search-field-result" id="searchId"></td>
        </tr>
        <tr class="search-field">
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_GIV_NAME") ?></td>
            <td class="search-field-result" id="searchGivName"></td>
        </tr>
        <tr class="search-field">
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_FAM_NAME") ?></td>
            <td class="search-field-result" id="searchFamName"></td>
        </tr>
        <tr class="search-field">
            <td><?= GetMessage("TR_ID_PERSONAL_COL_TN_EMAIL") ?></td>
            <td class="search-field-result" id="searchEmail"></td>
        </tr>

        <tr class="search-field-error" align="center" style="display: none">
                <td colspan="2">
                    <div class="search-field-message adm-info-message">

                    </div>
                </td>
        </tr>
        <input type="hidden" name="id" id="id" value="<?= $bxId ?>">
        <input type="hidden" name="trId" id="trId">
    </form>

    <?
    $tabControl->Buttons(
        array(
            "disabled" => false,
            "back_url" => "trusted_id_users.php?lang=" . LANG,
        )
    );

    $tabControl->End();
?>

<style>
    .search-field {
        display: none;
    }
</style>

<script>
    function findValue() {
        function showSearchResultsFields(data) {
            var resultsElements = document.getElementsByClassName("search-field");
            for (var el of resultsElements) {
                el.style.display = "table-row";
            }
            console.log(data);
            document.getElementById("searchId").innerHTML = data.entityId;
            document.getElementById("searchGivName").innerHTML = data.givenName;
            document.getElementById("searchFamName").innerHTML = data.familyName;
            document.getElementById("searchEmail").innerHTML = data.email;

            document.getElementById("trId").value = data.entityId;

            if(data.email === null) {
                document.getElementsByClassName("search-field-error")[0].style.display = "table-row";
                document.getElementsByClassName("search-field-message")[0].innerHTML = '<?= GetMessage("TR_ID_PERSONAL_PERSONAL_DATA_IN_NOT_GIVEN") ?>' + data.entityId +  '<?= GetMessage("TR_ID_PERSONAL_PERSONAL_DATA_IN_NOT_GIVEN2") ?>';
            }
        }

        function hideSearchResultsFields() {
            var resultsElements = document.getElementsByClassName("search-field");
            for (var el of resultsElements) {
                el.style.display = "none";
                el.getElementsByClassName("search-field-result")[0].innerHTML = "";
            }
        }

        function showErrorMessage(resp) {
            var errorMessage;
            if (resp.message === "Value cannot be empty!") {
                errorMessage = "<?= GetMessage("TR_ID_PERSONAL_VALUE_CANNOT_BE_EMPTY") ?>";
            } else if (resp.data == null) {
                errorMessage = "<?= GetMessage("TR_ID_PERSONAL_PPL_DOES_NOT_EXIST") ?>";
            }

            var errorElement = document.getElementsByClassName("search-field-error")[0];
            errorElement.style.display = "table-row";

            var errorFieldMessageElement = errorElement.getElementsByClassName("search-field-message")[0];
            errorFieldMessageElement.innerHTML = errorMessage;
        }

        function hideErrorMessage() {
            var errorElement = document.getElementsByClassName("search-field-error")[0];
            errorElement.style.display = "none";

            var errorFieldMessageElement = errorElement.getElementsByClassName("search-field-message")[0];
            errorFieldMessageElement.innerHTML = "";
        }

        var searchTypeValue = document.getElementById("SelectBoxValue").value;
        var searchValue = document.getElementById("findUserValue").value;
        sendCommand("find", {searchTypeValue, searchValue}, (response, err) => {
            // Some times data will as a string not a JSON
            if (err || response.success === false || response.data == null) {
                console.log(err || response);

                hideSearchResultsFields();
                showErrorMessage(response);
            } else {
                hideErrorMessage();
                showSearchResultsFields(response.data);
            }
        })
    }

    function removeUserBinding() {
        var userID = <?= $personalData["BX_ID"] ?>;

        sendCommand("remove", {userID}, (data, err) => {
            if (err || data.success == false) {
                console.log(err || data);
            } else {
                document.getElementById("personalTnUID").style.display = "none";
                document.getElementById("personalTnGivName").style.display = "none";
                document.getElementById("personalTnFamName").style.display = "none";
                document.getElementById("personalTnEmail").style.display = "none";
                document.getElementById("btnRmvBind").style.display = "none";
            }
        });
    }

    function sendCommand(command, params, cb) {
        BX.ajax.get(
            "trusted_id_ajax.php",
            Object.assign({command: command}, params),
            function (data, err) {
                if (err) {
                    cb(data, err);
                } else {
                    try {
                        var jsonData = JSON.parse(data);
                        cb(jsonData, err);
                    } catch (e) {
                        cb(data, err);
                    }
                }

            }
        );
    }
</script>

<?

CJSCore::Init(['ajax']);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");

?>
