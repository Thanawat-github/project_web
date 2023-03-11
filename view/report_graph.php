<?php

if (!isset($_SESSION['id_teacher'])) {

    $_SESSION['error'] = "กรุณาเข้าสู่ระบบใหม่อีกครั้ง!";
    echo "<script>window.location.href='auth/login.php';</script>";
    exit;
}

if (isset($_GET['subid'])) {

    $subid = $_GET['subid'];

    $idinclass = $lms->select('sub_std', "id_student", "id_subject='$subid'");
    $idinc = array();
    foreach ($idinclass as $value) {
        $idinc[] = $value['id_student'];
    }
} else {

    $_SESSION['error'] = "เกิดข้อผิดพลาด! ไม่พบข้อมูล!";
    echo "<script> window.history.back()</script>";
    exit;
}

?>
<style>
    .spinner-wrapper {
        background-color: #000;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .spinner-border {
        height: 60px;
        width: 60px;
    }
</style>
<div class="spinner-wrapper text-primary" id="loadp">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
    &nbsp;<h4>&nbsp;Loading...</h4>
</div>
<div class="album py-5 " style="background-color:#f0f8ff;">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">หน้าหลัก</a></li>
                <li class="breadcrumb-item"><a href="?page=report.php">รายงาน</a></li>
                <li class="breadcrumb-item active" aria-current="page">กราฟรายงาน</li>
            </ol>
        </nav>
        <div class="row mb-4 d-flex justify-content-center">
            <div class="col-sm-5">
                <h2>รายงานภาพการเข้าเรียน</h2>
            </div>
            <div class="col-sm-5 text-end">
                <a class="btn btn-primary" href="?page=report&subid=<?= $subid ?>"><i class="fa-regular fa-circle-left"></i>&nbsp;กลับ</a>
            </div>
        </div>
        <div class="px-5 py-4 bg-light rounded-5 shadow-lg">
            <div class="row">
                <div class="col-lg-12">
                    <div class="main-box clearfix">
                        <div id="parent1" style="display:flex;background-color:#f0f8ff;min-height:740px;">
                            <div class="margin" style="position: relative; float:center; margin: 50px 0px 0px 30px;">
                                <image id="imgDisplay" style="width: 750; height: 525;" src="upload/img_cap5min/29-155-20230312021935.png">
                                    <canvas id="overlay" style="position: absolute; top: 0; left: 0;" width="750" height="525" /></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('#loadp').show();
    Promise.all([
        faceapi.nets.faceRecognitionNet.loadFromUri('/project_web/data/models'),
        faceapi.nets.faceLandmark68Net.loadFromUri('/project_web/data/models'),
        faceapi.nets.ssdMobilenetv1.loadFromUri('/project_web/data/models'),
        faceapi.nets.tinyFaceDetector.loadFromUri('/project_web/data/models')
    ]).then(start)

    async function start() {
        $.ajax({
            datatype: 'json',
            url: "http://localhost/project_web/php/fetch.php",
            data: ""
        }).done(async function(data) {
            if (data.length > 2) {
                var json_str = "{\"parent\":" + data + "}"
                content = JSON.parse(json_str)
                for (var x = 0; x < Object.keys(content.parent).length; x++) {
                    for (var y = 0; y < Object.keys(content.parent[x]._descriptors).length; y++) {
                        var results = Object.values(content.parent[x]._descriptors[y])
                        content.parent[x]._descriptors[y] = new Float32Array(results)
                    }
                }
                faceMatcher = await createFaceMatcher(content);
            }
            $('#loadp').hide();
            onPlay();
        });
    }

    async function createFaceMatcher(data) {
        var idinc = <?= json_encode($idinc) ?>;

        const labeledFaceDescriptors = await Promise.all(data.parent.map(className => {
            const descriptors = [];
            for (var i = 0; i < className._descriptors.length; i++) {
                descriptors.push(className._descriptors[i]);
            }
            if (idinc.includes(className.std_id)) {
                label = className._label;
            } else {
                label = 'unknown';
            }
            return new faceapi.LabeledFaceDescriptors(label, descriptors);

        }))
        return new faceapi.FaceMatcher(labeledFaceDescriptors, 0.6);
    }

    function dtfetch() {
        var resultt = $.ajax({
            datatype: 'json',
            url: "http://localhost/project_web/php/fetch.php",
            data: ""
        }).done(function(data) {
            console.log('fetch success');
        });

        return resultt;
    }

    var dataFetch = undefined;
    //asyncCall();
    async function onPlay() {

        if (dataFetch == undefined) {
            dataFetch = await dtfetch();
        }
        var dtf = JSON.parse(dataFetch);

        $("#overlay").show();
        const canvas = $('#overlay').get(0)

        if (faceMatcher != undefined) {
            //--------------------------FACE RECOGNIZE------------------
            const input = document.getElementById('imgDisplay')
            const displaySize = {
                width: 750,
                height: 525
            }
            faceapi.matchDimensions(canvas, displaySize)
            const detections = await faceapi.detectAllFaces(input).withFaceLandmarks().withFaceDescriptors()
            const resizedDetections = faceapi.resizeResults(detections, displaySize)
            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor))
            console.log('111');
            console.log(results);
            results.forEach((result, i) => {
                const box = resizedDetections[i].detection.box
                const drawBox = new faceapi.draw.DrawBox(box, {
                    label: result.toString()
                })
                drawBox.draw(canvas)
                console.log(result.toString())
                var str = result.toString()
                rating = parseFloat(str.substring(str.indexOf('(') + 1, str.indexOf(')')))
                str = str.substring(0, str.indexOf('('))
                str = str.substring(0, str.length - 1)

            });
        }
    }
</script>