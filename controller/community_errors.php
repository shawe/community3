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

/**
 * Description of community_home
 *
 * @author carlos
 */
class community_errors extends fs_controller
{
   public $page_title;
   public $page_description;
   public $pendientes;
   public $resultados;
   public $rid;
   
   private $offset;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Errores', 'comunidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $this->pendientes = !isset($_REQUEST['all']);
      
      $item = new comm3_item();
      if($this->pendientes)
      {
         $this->resultados = $item->pendientes_by_tipo('error', $this->offset);
      }
      else
         $this->resultados = $item->all_by_tipo('error', $this->offset);
   }
   
   protected function public_core()
   {
      $this->page_title = 'Errores &lsaquo; Comunidad FacturaScripts';
      $this->page_description = 'Informes de error de FacturaScripts.';
      $this->template = 'public/errors';
      
      $this->rid = FALSE;
      if( isset($_COOKIE['rid']) )
      {
         $this->rid = $_COOKIE['rid'];
      }
      
      $this->offset = 0;
      if( isset($_GET['offset']) )
      {
         $this->offset = intval($_GET['offset']);
      }
      
      $item = new comm3_item();
      $this->resultados = $item->all_by_tipo('error', $this->offset);
   }
   
   public function anterior_url()
   {
      $url = '';
      
      if($this->offset > 0)
      {
         $url = $this->url()."&offset=".($this->offset-FS_ITEM_LIMIT);
      }
      
      if(!$this->pendientes)
      {
         $url .= '&all=TRUE';
      }
      
      return $url;
   }
   
   public function siguiente_url()
   {
      $url = '';
      
      if( count($this->resultados) == FS_ITEM_LIMIT )
      {
         $url = $this->url()."&offset=".($this->offset+FS_ITEM_LIMIT);
      }
      
      if(!$this->pendientes)
      {
         $url .= '&all=TRUE';
      }
      
      return $url;
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
