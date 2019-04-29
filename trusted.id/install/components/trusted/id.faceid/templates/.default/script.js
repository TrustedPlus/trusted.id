let lastTryTime;
let currentlyTime;

window.onload = function () {
    if (keyExist) {
        currentlyTime = Date.now();
        lastTryTime = currentlyTime - timeStep;
        tracker();
    }
};

function tracker() {
    let video = document.getElementById('video');
    let canvas = document.getElementById('canvas');
    let context = canvas.getContext('2d');
    let tracker = new tracking.ObjectTracker('face');

    tracker.setInitialScale(4);
    tracker.setStepSize(2);
    tracker.setEdgesDensity(0.1);

    tracking.track(video, tracker, {camera: true});
    tracker.on('track', function (event) {
        context.clearRect(0, 0, canvas.width, canvas.height);
        event.data.forEach(function (rect) {
            context.strokeStyle = '#a64ceb';
            context.strokeRect(rect.x, rect.y, rect.width, rect.height);
            checkFace();
        });
    });
}

function checkFace() {
    canvas.getContext('2d').drawImage(document.getElementById('video'), 0, 0, this.canvas.width, this.canvas.height);
    let imgFace = canvas.toDataURL("image/jpeg");
    checkCurrentlyTime(imgFace);
}

function checkCurrentlyTime(imgFace) {
    currentlyTime = Date.now();
    if ((currentlyTime - lastTryTime) >= timeStep) {
        lastTryTime = currentlyTime;
        findUserByPhoto(imgFace);
    }
}

function findUserByPhoto(imgFace) {
    BX.ajax({
            url: ajaxUrl,
            data: {searchTypeValue: 'photo', searchValue: imgFace},
            method: 'POST',
            onsuccess: function (response) {
                const data = JSON.parse(response);
                if (data.success === true) {
                    if (data.data) {
                        let userName = data.data['displayName'];
                        let userId = data.data['entityId'];
                        console.log(userId);
                        document.getElementById('userFIO').innerHTML = userName;
                        document.getElementById('userId').value = userId;
                    } else {
                        console.log('Unknown object');
                        document.getElementById('userFIO').innerHTML = '<?= Loc::getMessage("TR_ID_COMP_FACEID_UNKNOWN_USER")?>';
                        document.getElementById('userId').value = null;
                        return false;
                    }
                }
                return true;
            },
            onfailure: function (err) {
                console.log(err);
                return false;
            }
        }
    );
}