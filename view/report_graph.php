<?php

if (!isset($_SESSION['id_teacher'])) {

    $_SESSION['error'] = "กรุณาเข้าสู่ระบบใหม่อีกครั้ง!";
    echo "<script>window.location.href='auth/login.php';</script>";
    exit;
}

if (isset($_GET['repid']) && isset($_GET['subid'])) {

    $repid = $_GET['repid'];
    $subid = $_GET['subid'];

    $idinclass = $lms->select('sub_std', "id_student", "id_subject='$subid'");
    $idinc = array();
    foreach ($idinclass as $value) {
        $idinc[] = $value['id_student'];
    }
    $numstd = $lms->select('sub_std', "count(*) as numstd", "id_subject='$subid'");
    $time_cap = $lms->select("checkcap", "time_cap", "id_croom='$repid'");
    $tcap = array();
    foreach ($time_cap as $val) {
        $tcap[] = $val['time_cap'];
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
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        <?php
                        $report_graph = $lms->select("checkcap", "*", "id_croom='$repid'");
                        $numindex = 0;
                        foreach ($report_graph as $rep_graph) {
                            $numindex++;
                        ?>
                            <image id="imgDisplay<?= $numindex; ?>" style="width: 700; height: 525; display:none;" src="upload/img_cap5min/<?= $rep_graph['path_cap']; ?>" />
                            <!-- <canvas id="overlay" style="position: absolute; top: 0; left: 0;" width="700" height="525" /> -->
                        <?php } ?>
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var dataFetch = undefined;
    var student_total = [];
    var cap_total = [];
    var time_cap = <?= json_encode($tcap) ?>;
    console.log(time_cap);
    var numindex = <?= $numindex; ?>;
    var ix = 1;

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
            if (dataFetch == undefined) {
                dataFetch = await dtfetch();
            }
            var dtf = JSON.parse(dataFetch);
            await numimg();
            console.log(student_total);

            $('#loadp').hide();
            const ctx = document.getElementById('myChart');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: cap_total,
                    datasets: [{
                        label: 'จำนวนนักเรียนที่เรียน',
                        data: student_total,
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            max: <?= $numstd[0]['numstd']; ?>
                        }
                    },
                    ticks: {
                        precision: 0
                    }
                }
            });

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

    async function numimg() {

        for (ix; ix <= numindex; ix++) {
            cap_total[ix - 1] = 'นาทีที่ : ' + (5 * ix)+" เวลา : "+time_cap[ix-1];
            await onPlay();
        }
    }

    async function onPlay() {
        console.log('this round ' + ix);
        var std_total = 0;

        // if (dataFetch == undefined) {
        //     dataFetch = await dtfetch();
        // }
        // var dtf = JSON.parse(dataFetch);

        //$("#overlay").show();
        //const canvas = $('#overlay').get(0)

        if (faceMatcher != undefined) {
            //--------------------------FACE RECOGNIZE------------------
            const input = $('#imgDisplay' + ix).get(0);
            const displaySize = {
                width: 700,
                height: 525
            }
            //faceapi.matchDimensions(canvas, displaySize);
            const detections = await faceapi.detectAllFaces(input).withFaceLandmarks().withFaceDescriptors();
            const resizedDetections = faceapi.resizeResults(detections, displaySize);
            const results = resizedDetections.map(d => faceMatcher.findBestMatch(d.descriptor));
            console.log(results);
            if (results.length > 1) {
                results.forEach((result, i) => {
                    // const box = resizedDetections[i].detection.box;
                    // const drawBox = new faceapi.draw.DrawBox(box, {
                    //     label: result.toString()
                    // });
                    // drawBox.draw(canvas);
                    console.log(result.toString());
                    var str = result.toString();
                    rating = parseFloat(str.substring(str.indexOf('(') + 1, str.indexOf(')')));
                    str = str.substring(0, str.indexOf('('));
                    str = str.substring(0, str.length - 1);
                    if (str != "unknown") {
                        std_total++;
                    }

                });
            } else {
                console.log('no detect')
            }
        }
        student_total[ix - 1] = std_total
    }
</script>