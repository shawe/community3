<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_model('comm3_item.php');
require_model('comm3_visitante.php');

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_feedback extends fs_controller
{
   public $feedback_email;
   public $feedback_type;
   public $feedback_text;
   public $feedback_info;
   public $feedback_privado;
   public $page_title;
   public $page_description;
   public $visitante;
   
   private $rid;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Feedback', 'comunidad', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      
   }
   
   protected function public_core()
   {
      $this->page_title = 'Feedback &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Aporta feedback a la comunidad FacturaScripts.';
      $this->template = 'public/feedback';
      $this->feedback_email = '';
      $this->feedback_type = 'question';
      $this->feedback_text = '';
      $this->feedback_info = '';
      $this->feedback_privado = FALSE;
      $visit0 = new comm3_visitante();
      $this->visitante = FALSE;
      
      /**
       * Necesitamos un identificador para el visitante.
       * Así luego podemos relacioner sus comentarios y preguntas.
       */
      $this->rid = $this->random_string(30);
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
         $this->visitante = $visit0->get_by_rid($this->rid);
         if($this->visitante)
         {
            $this->feedback_email = $this->visitante->email;
         }
      }
      else
      {
         setcookie('rid', $this->rid, time()+FS_COOKIES_EXPIRE, '/');
      }
      
      if( isset($_POST['feedback_type']) )
      {
         if( isset($_POST['feedback_email']) )
         {
            $this->feedback_email = $_POST['feedback_email'];
         }
         $this->feedback_type = $_POST['feedback_type'];
         $this->feedback_text = $_POST['feedback_text'];
         $this->feedback_info = $_POST['feedback_info'];
         $this->feedback_privado = isset($_POST['feedback_privado']);
         
         if($this->feedback_email == '')
         {
            $this->new_error_msg('Debes escribir tu email, es obligatiorio.');
         }
         else if( !filter_var($this->feedback_email, FILTER_VALIDATE_EMAIL) )
         {
            $this->new_error_msg('Email no válido. Revísalo.');
         }
         else if($_POST['feedback_human'] != '')
         {
            $this->new_error_msg('Debes borrar el número para demostrar que eres humano.');
         }
         else
         {
            /// necesitamos un visitante para guardar algo
            if( !$this->visitante )
            {
               $this->visitante = new comm3_visitante();
               $this->visitante->rid = $this->rid;
               $this->visitante->email = $this->feedback_email;
            }
            
            $item = new comm3_item();
            $item->email = $this->visitante->email;
            $item->rid = $this->visitante->rid;
            $item->tipo = $this->feedback_type;
            $item->privado = $this->feedback_privado;
            $item->texto = $this->feedback_text;
            $item->info = $this->feedback_info;
            
            if( isset($_SERVER['REMOTE_ADDR']) )
            {
               $this->visitante->last_ip = $_SERVER['REMOTE_ADDR'];
               $item->ip = $_SERVER['REMOTE_ADDR'];
            }
            
            if( isset($_SERVER['HTTP_USER_AGENT']) )
            {
               $this->visitante->last_browser = $_SERVER['HTTP_USER_AGENT'];
               $item->info .= $_SERVER['HTTP_USER_AGENT'];
            }
            
            if( $this->visitante->save() )
            {
               if( $item->save() )
               {
                  $this->new_message('Datos guardados correctamente.');
                  header('Location: '.$item->url() );
               }
               else
                  $this->new_error_msg('Error al guardar los datos 2.');
            }
            else
               $this->new_error_msg('Error al guardar los datos.');
         }
      }
      else if( isset($_GET['feedback_type']) )
      {
         $this->feedback_type = $_GET['feedback_type'];
      }
   }
   
   public function path()
   {
      if( defined('COMM3_PATH') )
      {
         return COMM3_PATH;
      }
      else
         return '';
   }
}
