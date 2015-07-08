<?php

return function($data){
    return 'chat id: '.$data->{'message'}->{'chat'}->{'id'};
};
