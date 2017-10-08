<?php
/**
 * User: Alexander Popov <b059ae@gmail.com>
 * Date: 08.10.2017
 * Time: 13:31
 */
/** @var $yaCounter string */
/** @var $options string */
/** @var $disableWhenDebug string */
?>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
    function metrikaReachGoal(name, params) {
        <?php if ($disableWhenDebug && !YII_DEBUG): ?>
        if (typeof yaCounter<?=$yaCounter?> != "undefined") {
            if (typeof params == "undefined") {
                params = {};
            }
            yaCounter<?=$yaCounter?>.reachGoal(name, params);
        }
        <?php endif; ?>
    }
</script>
<?php if ($disableWhenDebug && !YII_DEBUG): ?>
    <script type="text/javascript">
        (function (d, w, c) {
            (w[c] = w[c] || []).push(function () {
                try {
                    w.yaCounter<?=$yaCounter?> = new Ya.Metrika(<?=$options?>);
                } catch (e) {
                }
            });

            var n = d.getElementsByTagName("script")[0],
                s = d.createElement("script"),
                f = function () {
                    n.parentNode.insertBefore(s, n);
                };
            s.type = "text/javascript";
            s.async = true;
            s.src = "https://mc.yandex.ru/metrika/watch.js";

            if (w.opera == "[object Opera]") {
                d.addEventListener("DOMContentLoaded", f, false);
            } else {
                f();
            }
        })(document, window, "yandex_metrika_callbacks");
    </script>
    <noscript>
        <div><img src="https://mc.yandex.ru/watch/<?=$yaCounter?>" style="position:absolute; left:-9999px;" alt=""/></div>
    </noscript>
    <!-- /Yandex.Metrika counter -->
<?php endif; ?>

