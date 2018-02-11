FROM php:7.2.1-cli-stretch

RUN echo "<?php while(true){ echo 1; sleep(1); }" > /while.php
CMD ["php", "/while.php"]