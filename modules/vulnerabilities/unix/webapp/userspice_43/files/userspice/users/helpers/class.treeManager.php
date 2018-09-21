<?php
/**
 * File:        class.treeManager.php
 * Date:        Wed 30 Aug 2009 10:30:38 AM CET
 * Author:      Leon van Kammen
 * Function:    class which makes life easier when it comes to storing/retrieving hierarchical
 *              arrays (trees) from the database. When you use this, you will be saved from
 *              lots of recursive mayhem ;]
 *
 *              the idea is to do two-way conversion of tree's and indented lists easily.
 *              Why? So you can easily store the structure in the database, and easily render
 *              this structure into a html selectionbox/menus etc. Think of it as some kind of
 *              'serialize/unserialize' function, but with having the search-benefits of SQL.
 *              I hope you have fun slappin those trees :]
 * Greets:      IZI Services, Mannetje, Boompje
 * License:     BSD LICENSE
 *              Copyright (c) 2009, Leon van Kammen All rights reserved.
 *
 *              Redistribution and use in source and binary forms, with or without modification,
 *              are permitted provided that the following conditions are met:
 *
 *                  * Redistributions of source code must retain the above copyright notice,
 *                    this list of conditions and the following disclaimer.  i
 *                  * Redistributions in binary form must reproduce the above copyright notice,
 *                    this list of conditions and the following disclaimer in the documentation
 *                    and/or other materials provided with the distribution.
 *                  * Neither the name of the 'treeManager' or 'Leon van Kammen' nor the names of
 *                    its contributors may be used to endorse or promote products derived from
 *                    this software without specific prior written permission.
 *
 *              THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *              ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *              WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 *              DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 *              ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 *              (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 *              LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 *              ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 *              (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 *              SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * Changelog:
 *
 * 	[Wed 30 Aug 2009 10:30:38 AM CET]
 *		first sketch
 *
 * @todo description
 *
 * Usage example:
 * <code>
 *      Example, suppose your sql table layout is :
 *
 *      [ id(int11) | parent_id(int11) | weight(int11) | title_menu | content(text ]
 *
 *      <?
 *          $treeManager     = treeManager::get();
 *          $records         = $db->getArrayFromSql( "SELECT * FROM mytable" );
 *          // here we have our multidimensional, weightsorted tree!
 *          $recordsTree     = $treeManager->getTree( 'id', 'parent_id', 'weight', $records );
 *          // here we have this tree slapped into a one-dimensional indented list
 *          $recordsSlapped  = $treeManager->slapTree( $records );
 *          // and vice versa!
 *          $recordsTree     = $treeManager->getTree( 'id', 'parent_id', 'weight', $recordsSlapped );
 *          // or wait, lets view our tree in text/html!
 *          foreach( $recordsSlapped as $node )
 *            echo "{$node['title_menu_indent']}\n";
 *      ?>
 * </code>
 *
 */

class treeManager{

  /**
   * Singleton      - get instance by calling treeManager::get()
   */
  private static $instance;
  public static function get() {
    return (!treeManager::$instance instanceof self) ? treeManager::$instance = new self() : treeManager::$instance;
    }

  /**
   * getTree            - recursive function which can create an tree structure out of an sql table.
   *                      it also automatically sorts elements by weight.
   * @param mixed $id_key
   * @param mixed $parent_id_key
   * @param mixed $weight_key
   * @param mixed $array
   * @access public
   * @return void
   */
  function getTree( $array, $id_key = 'id', $parent_id_key = 'parent_id', $weight_key = 'weight' ){
 global $us_url_root;

    _assert( is_array( $array ), "need array!" );
    // assign children to every element
    foreach( $array as $index => $element )
      $array[ $index ]['children'] = $this->getChildren( $element[ $id_key ], $parent_id_key, $id_key, $weight_key, $array );
    // now remove non-parent nodes from root
    foreach( $array as $index => $element )
      if( $element[ $parent_id_key ] > 0 )
        unset( $array[ $index] );
    return $array;
  }

  /**
   * getChildren        - recursive function which can create an tree structure out of an sql table.
   *                      callers: getTree()
   *
   * @param mixed $parent_id_value    this is the parent id value to look for
   * @param mixed $parent_id_key      this is the parent_id_key of each element
   * @param mixed $id_key             this is the id key of each element
   * @param mixed $weight_key         this is the 'weight' key of the array
   * @param mixed $array              this is the array which will be used as input
   * @access public
   * @return array                    the children of this parent id
   */
  function getChildren( $parent_id_value, $parent_id_key, $id_key, $weight_key, $array ){
    if( !is_array( $array ) ) return false;
    $children = array();
    // check if children look for their parent
    foreach( $array as $key => $item ){
      _assert( isset( $item[ $parent_id_key ] ), "parent_id key '{$parent_id_key}' not set in each array element...cannot build tree");
      if( $item[ $parent_id_key ] == $parent_id_value ){
        $weight     = $item[ $weight_key ];
        while( isset( $children[ $weight ] ) )
          $weight++;
        $children[ $weight ] = $item;
      }
    }
    // check if children have children
    foreach( $children as $key => $child ){
      $children[ $key ]['children'] = $this->getChildren( $child[ $id_key ], $parent_id_key, $id_key, $weight_key, $array );
    }
    ksort( $children );
    return $children;
  }

  /**
   * slapTree                        - converts an multidimensional array to a single list (SLAP!!)
   *                                    This is handy when you want to create options for a selectbox,
   *                                    which represent the content of an hierarchic structure (from getChildren())
   *                                    These examples will explain the input an output (YAML notation of arrays)
   *
   *                                    [INPUT]   - nodename: pep
   *                                                children:
   *                                                  - nodename: flop
   *                                                    children:
   *                                                      - nodename: deep
   *                                    [OUTPUT]  - nodename: pep
   *                                                indent: 0
   *                                              - nodename: flop
   *                                                indent: 1
   *                                              - nodename: deep
   *                                                indent: 2
   *
   * @param mixed $array              the multidimensional array
   * @param int $indentSize           influences the space of one indent
   * @param string $indentKey         which key to use as indentation label?
   * @param string $glue              which glue ? (for example "/" )
   * @param string $spacer            how will we do indents? " " or "&nbsp" ?
   * @param mixed $child_key          the key name of array elements which (might) contain children elements
   * @access public
   * @return void
   */
  function slapTree( $array, $indentSize = 3, $indentKey = "title_menu", $glue = "/", $spacer = " ",$child_key = "children", $firsttime = true )
  {
    global $slappedArray;
    if( !is_array($slappedArray) || $firsttime ){
      $slappedArray = array();
      $array = $this->addIndents( $array, $child_key, $indentSize, $indentKey, $glue, $spacer );
    }
    if( !is_array( $array ) ) return false;
    if( is_array( $array ) ){
      foreach( $array as $key => $element ){
        $children       = $element[ $child_key ];
        unset( $element[ $child_key ] );
        $slappedArray[] = $element;
        if( is_array( $children ) )
          $this->slapTree( $children, $indentSize, $indentKey, $glue, $spacer, $child_key, false );
      }
    }
    return $slappedArray;
  }

  /**
   * addIndents                      - Recursive function which adds an indentation to multidimensional arrays.
   *                                   It adds a 'indent' attribute to each element with indentation information.
   *                                   This is handy....yes..because you can use this to set margin's/paddings/indents
   *                                   in your html output.
   *                                   callers: slapTree()
   *
   * @param mixed $array             input array
   * @param mixed $child_key         the key-name under which the children are stored (for eg. 'childs' as in $array[0]['childs'])
   * @param int $indentSize          size of indentation...this can be used to control margins/paddings/indentations later on
   * @param string $indentKey        which key to use as indentation label?
   * @param string $glue             which glue ? (for example "/" )
   * @param string $spacer           how will we do indents? " " or "&nbsp" ?
   * @param mixed $child_key         the key name of array elements which (might) contain children elements
   * @param float $level             do not pass this arguments since its a private recursive var
   * @access public
   * @return void
   */
  function addIndents( $array, $child_key, $indentSize, $indentKey, $glue,  $spacer, $_level = 1 ){
    global $path;
    if( !is_array( $array ) ) return false;
    foreach( $array as $key => $element ){
      if( isset( $element['indent'] ) ) return;
      if( $_level == 1 ){
        $array[ $key ]['indent'] = 0;
        $array[ $key ][ "{$indentKey}_indent" ]   = $glue . $element[ $indentKey ];
        $array[ $key ][ "{$indentKey}_path" ]     = $glue . $element[ $indentKey ];
      }
      if( is_array( $element[ $child_key ] ) ){
        $path_bak  = $path;
        $path     .= $glue . $element[ $indentKey ];
        $array[ $key ][ $child_key ] = $this->addIndents( $array[ $key ][$child_key ], $child_key, $indentSize, $indentKey, $glue, $spacer, $_level + 1 );
        foreach( $element[ $child_key ] as $k => $child ){
          $array[ $key ][ $child_key ][$k]["indent"]                = $indent = $_level * $indentSize;
          $array[ $key ][ $child_key ][$k]["{$indentKey}_path"]     = $path . $glue . $child[ $indentKey ];
          $array[ $key ][ $child_key ][$k]["{$indentKey}_indent"]   = "{$glue} {$child[ $indentKey ]}";
          for( $i = 0; $i < (int)$indent; $i++ )
            $array[ $key ][ $child_key ][$k]["{$indentKey}_indent"] = $spacer . $array[ $key ][ $child_key ][$k]["{$indentKey}_indent"];
        }
        $path = $path_bak;
      }
    }
    return $array;
  }

  /**
   * moveNode               - moves a node up or down relative to its parents & neighbours
   *
   * @param string $action   "up" or "down"
   * @param mixed $id_key    search for array element with this key
   * @param mixed $id_value  and with this value
   * @access public
   * @return array           returns array of 2 elements which should be updated
   */
  function moveNode( $action, $id_key, $id_value, $array, $weight_key = "weight" ){
    $candidate    = false;
    $neighbour    = false;
    $brothers     = array();
    // search our beloved candidate
    foreach( $array as $key_candidate => $node )
      if( $node[ $id_key ] == $id_value )
        $candidate = $node;
    // search for his brothers
    foreach( $array as $key_candidate => $node )
      if( $candidate['parent_id'] == $node['parent_id'] )
        $brothers[] = $node;
    // if candidate found lets try to find nearest neighbour
    for( $i = 0; $i < count($brothers); $i++ ){
      if( $brothers[ $i ]['id'] == $candidate['id'] ){
        $i = ( $action == "up" ) ? $i - 1 : $i + 1;
        break;
      }
    }
    $neighbour = ( $i >= 0 || $i < count($brothers) ) && isset( $brothers[$i] ) ? $brothers[$i] : false;
    // lets swap weights
    if( $candidate && $neighbour ){
       $neighbour_weight          = $neighbour[ $weight_key ];
       $neighbour[ $weight_key ]  = $candidate[ $weight_key ];
       $candidate[ $weight_key ]  = $neighbour_weight;
    }
    return ( $neighbour &&  $candidate ) ? array( $candidate, $neighbour ) : false;
  }
}

?>
