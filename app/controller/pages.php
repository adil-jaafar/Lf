<?php


$index = function () use ( &$Html ) {
	$Html->render( 'pages/index' , null , 'layouts/default');
};
