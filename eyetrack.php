<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>tracking.js - face hello world</title>

    <script src="build/tracking-min.js"></script>
    <script src="build/data/eye-min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.1.js"></script>


</head>

<body>
    <div id="parent1" style="display:flex;background-color:#f0f8ff;min-height:740px;">
        <div class="margin" style="position: relative; float:center; margin: 50px 0px 0px 30px;">
            <video id="vidDisplay" style="width: 800px; height: 600px; display: inline-block; 
    vertical-align: baseline; border: 3px solid black;" autoplay="true"></video>
            <canvas id="canvas" style="position: absolute; top: 0; left: 0;" width="800" height="600" />
        </div>
    </div>

    <script>
        window.onload = function() {
            var canvas = document.getElementById('canvas');
            var context = canvas.getContext('2d');

            var tracker = new tracking.ObjectTracker(['eye']);
            tracker.setStepSize(1.7);

            tracking.track('#vidDisplay', tracker);
            //context.clearRect(0, 0, canvas.width, canvas.height);
            tracker.on('track', function(event) {
                context.clearRect(0, 0, canvas.width, canvas.height);

                event.data.forEach(function(rect) {

                    window.plot(rect.x, rect.y, rect.width, rect.height);

                });
            });


            window.plot = function(x, y, w, h) {

                context.strokeRect(x, y, w, h);
            };
        };

        async function run() {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {}
            })
            const videoEl = $('#vidDisplay').get(0)
            videoEl.srcObject = stream
        }
        run();
    </script>

</body>

</html>