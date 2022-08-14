<?php

/**
 * Plugin Name: rabin Callaback URL Receive
 * Author: Rabin
 * Version: 0.1.0

*/

add_action( 'rest_api_init', 'rabin_add_callback_url_endpoint' );

function rabin_add_callback_url_endpoint(){
    register_rest_route(
        'rabin/v1/', // Namespace
        'receive-callback', // Endpoint
        array(
            'methods'  => 'POST',
            'callback' => 'rabin_receive_callback'
        )
    );
}


function rabin_receive_callback( $request_data ) {
    $data = array();
    
    $parameters = $request_data->get_params();
    
    $name     = $parameters['name'];
    $password = $parameters['password'];
    $title    = $parameters['title'];
    $body     = $parameters['body'];
    
    if ( isset($name) && isset($password) ) {
        
        $userdata = get_user_by( 'login', $name );
        
        if ( $userdata ) {
            
            $wp_check_password_result = wp_check_password( $password, $userdata->user_pass, $userdata->ID );
            
            if ( $wp_check_password_result ) {
                $data['status'] = 'OK';
            
                $data['received_data'] = array(
                    'name'     => $name,
                    'password' => $password,
                    //'data'     => $userdata
                );
                
                $post_args = array(
                    'post_title'   => wp_strip_all_tags( $title ),
                    'post_content' => $body,
                    'post_status'  => 'publish',
                    'post_type'    => 'apidata'
                );
                
                $post_var = wp_insert_post( $post_args );
                
                if( $post_var ) {
                    $data['message'] = 'Post was successful';
                }
                
            } else {
                $data['status'] = 'OK';
                $data['message'] = 'You are not authenticated to login!';
            }
            
            
        } else {
            
            $data['status'] = 'OK';
            $data['message'] = 'The current user does not exist!';
        }
        
        
    } else {
        
        $data['status'] = 'Failed';
        $data['message'] = 'Parameters Missing!';
        
    }
    
    return $data;
}

function rabin_set_up_post_type(){
    
    $args = array(
        'public'             => true,
        'publicly_queryable' => false,
        'label'              => __( 'API Data', 'prefix-plugin-name' ),
        'menu_icon'          => 'dashicons-analytics',
        'supports'           => array( 'title', 'editor' )
    );
    
    register_post_type( 'apidata', $args );
}

add_action( 'init', 'rabin_set_up_post_type' );
