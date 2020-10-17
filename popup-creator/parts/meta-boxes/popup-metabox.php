<?php
/*
Title: Popup Settings
Post Type: popup
*/
piklist( 'field', array(
    'type'    => 'checkbox',
    'field'   => 'popupcreator_active',
    'label'   => __( 'Active', 'popupcreator' ),
    'value'   => 0,
    'choices' => array(
        1 => __( 'Active', 'popupcreator' ),
    ),
) );

piklist( 'field', array(
	'type'  => 'url',
	'field' => 'popupcreator_url',
	'label' => __( 'URL', 'popupcreator' ),
) );

piklist( 'field', array(
    'type'    => 'select',
    'field'   => 'popupcreator_popup_size',
    'label'   => __( 'Popup Size', 'popupcreator' ),
    'value'   => 'landscape',
    'choices' => array(
        'popup-landscape' => __( 'Landscape', 'popupcreator' ),
        'popup-square'    => __( 'Square', 'popupcreator' ),
        'full'            => __( 'Original', 'popupcreator' ),
    ),

) );