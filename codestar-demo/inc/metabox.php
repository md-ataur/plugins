<?php

function csf_demo_metabox($metaboxes){

	$metaboxes[] = array(
		'id'            => 'csf_metaboxes',
		'title'         => __('Book Info','codestar-demo'),
		'post_type'     => 'post',
		'context'       => 'normal',
		'priority'      => 'default',
		'sections'      => array(			
			array(
				'name'      => 'section-banner-one',
				//'title'     => __('Section One','codestar-demo'),
				'icon'      => 'fa fa-wifi',
				'fields'    => array(					
					array(
						'id'        => 'author',
						'type'      => 'text',
						'title'     => __('Book Author','codestar-demo'),
						
					),
					array(
						'id'    => 'year',
						'title' => __( 'Book Year', 'codestar-demo' ),
						'type'  => 'text',
					),
					array(
						'id'    => 'isbn',
						'title' => __( 'ISBN', 'codestar-demo' ),
						'type'  => 'text',
					)
				),
			),
		)
	);

	return $metaboxes;
}
add_filter( "cs_metabox_options", "csf_demo_metabox" );