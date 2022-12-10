<?php
/**
 * plugin name: Add Todo
 */

register_activation_hook(__FILE__, 'table_creator');
function table_creator(){
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
     $table_name = $wpdb->prefix . 'todolist';
     $sql = "DROP TABLE IF EXISTS $table_name;
     CREATE TABLE $table_name(
        id mediumint(11) NOT NULL AUTO_INCREMENT,
        todo_add varchar(200) NOT NULL,
        PRIMARY KEY id(id)
        )$charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);     
}
add_shortcode('frontend_crud','da_todo_callback');
function da_todo_callback()
{
    global $wpdb;
    $table_name=$wpdb->prefix.'todolist';
    $msg='';
    if(@$_REQUEST['action'] == 'submit'){
        $wpdb->insert("$table_name",[
         'todo_add'=>sanitize_text_field($_REQUEST['todo_add']),
        ]);
        if($wpdb->insert_id>0){
            $msg="Saved Successfully";
        }else{
            $msg="Failed to save data";
        }
    }

    if(@$_REQUEST['action'] == 'update_todo' && @$_REQUEST['id']){

        $id = @$_REQUEST['id'];

        if(@$_REQUEST['todo_add']){
            $update = $wpdb->update("$table_name",[
                'todo_add' =>sanitize_text_field($_REQUEST['todo_add'])],
                
                ['id' => $id]);

            if($update){
                $msg = "Data Updated <a href='".get_page_link(get_the_ID())."'>Add Todo</a>";
            }

        }

        $todos = $wpdb->get_row($wpdb->prepare("select * from $table_name where id = %d", $id), ARRAY_A);

        $todo_add = $todos['todo_add'];
    
    }
    if(@$_REQUEST['action'] == 'delete_todo' && @$_REQUEST['id']){
        $id=@$_REQUEST['id'];
        if($id){
            $row_exits=$wpdb->get_row($wpdb->prepare("select * from $table_name where id = %d",$id),ARRAY_A);
            if(count($row_exits)>0){
                $wpdb->delete("$table_name",array('id'=>$id));
            }
        }
        ?>
        <script>
            location.href="<?php echo get_the_permalink() ?>";
        </script>
        <?php }
   
    
    ?>
    <div class="form-container">
        <h4><?php echo @$msg; ?></h4>
        <form action="" method="post">
        <p>
                    <label>ADD TODO</label>
                    <input type="text" name="todo_add" value="<?php echo @$todo_add; ?>">
        </p>
        <p>
                    <button type="submit" name="action" value="<?php echo (@$_REQUEST['action']=='update_todo')?'update_todo':'submit'; ?>"><?php echo (@$_REQUEST['action'] == 'update_todo')?'Update':'Submit';?></button>
                </p>
        </form>
    </div>
    <?php

    $todo_list = $wpdb->get_results("SELECT * FROM $table_name",ARRAY_A);
    $i=1;
    if($todo_list > 0){ ?>
    <div style="margin-top: 40px;">
    <table border="1" cellpadding="10">
<tr>
    <th>S.No.</th>
    <th>Todo List</th>
    <th>Action</th>
</tr>
<?php foreach ($todo_list as $index => $todos):
?>
<tr>
    <td><?php echo $i++; ?></td>
    <td><?php echo $todos['todo_add']; ?></td>

<td>
    <a href="?action=update_todo&id=<?php echo $todos['id'];?>">Update</a>
    <a href="?action=delete_todo&id=<?php echo $todos['id'];?>"onclick="return confirm('Are you sure to remove this record?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php }
}