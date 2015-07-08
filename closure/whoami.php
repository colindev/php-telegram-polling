<?php

return function($data){
    return 'from id: '.$data->{'message'}->{'from'}->{'id'};
};
