
<h1>Сообщения и комментарии</h1>

<?php if($this->user_id>0){ ?>
<div class="login-info">
  Вы вошли как <?= User::getOne($this->user_id)->facebook_name; ?>
  <a href="/index.php?action=logout">[выйти]</a>
</div>
<form action="/index.php?action=create" method="POST">
    <div class="form-group">
        <label for="text_comment">Текст сообщения</label>
        <textarea rows="6" id="text_comment" name="text_comment"></textarea>
    </div>
    <input type="submit" value="Добавить" />
</form>
<?php } else { ?>
<div class="msg">
    Для добавления и комментирования сообщений выполните 
    <a href="<?= $this->auth_url . '?' . urldecode(http_build_query($this->auth_params)) ?>">вход</a>
</div>
<?php } ?>

<div class="posts-container">
<?php
  $posts = $params['posts'];
  function renderRecord($posts, $level, $user_id){
  for($i=0; $i<count($posts); $i++){
  ?><div class="post" style="margin-left: <?= (($level>0)? $level*10:"0") ?>px;"
       id="<?= "rec".$posts[$i]->id ?>"><div class="post-header">
          <span class="post-id">
             #<?= $posts[$i]->id ?>
             <?php if ($level > 0){ ?>
             -&gt; <a href="#rec<?= $posts[$i]->parent_id ?>">#<?= $posts[$i]->parent_id ?></a>
             <?php } ?>
          </span>
          &nbsp; &bullet; &nbsp;
          <span class="post-user">
             <?= $posts[$i]->getUser()->facebook_name ?>
          </span>
          &nbsp; &bullet; &nbsp;
          <span class="post-created_at">
              <?= $posts[$i]->created_at ?>
          </span>
      </div><div class="post-msg"><?= $posts[$i]->text_comment ?></div>
      <?php if($user_id>0){ ?>
      <div class="post-control">
          <form id="control<?= $posts[$i]->id ?>" action="/index.php" method="POST">
              <input type="hidden" name="id" value="<?= $posts[$i]->id ?>" />
              <input type="hidden" name="parent_id" value="<?= $posts[$i]->id ?>" />
              <select name="action" class="control-action">
                  <option value="">=действие=</option>
                  <option value="answer">ответить</option>
                  <?php if ($posts[$i]->user_id == $user_id){ ?>
                  <option value="edit">редактировать</option>
                  <?php } ?>
                  <?php if ($posts[$i]->terminal && $posts[$i]->user_id == $user_id){ ?>
                  <option value="delete">удалить</option>
                  <?php } ?>
              </select>
              <textarea id="answer_text<?= $posts[$i]->id ?>" name="text_comment" class="invisible"></textarea>

              <input type="submit" value="совершить" />
          </form>
      </div>
      <?php } ?>

      <?php
        if(!$posts[$i]->terminal){
            ?>
        <div class="comments">
          <?php 
            renderRecord($posts[$i]->comments, $level+1, $user_id);
          ?>
        </div>
            <?php
        } ?>
    </div>
  <?php
  }
  } 
  renderRecord($posts, 0, $this->user_id);
  ?>

</div>
<script>
    var objs = document.getElementsByClassName("control-action");
    for (var i = 0; i < objs.length; i++){
        var obj = objs[i];
        obj.onchange = function(){
            //console.log(this.nextElementSibling);
            this.nextElementSibling.className = "invisible";
            if(this.value === "answer"){
                this.nextElementSibling.className = "visible";
            }
            if(this.value === "edit"){
                this.nextElementSibling.className = "visible";
                this.nextElementSibling.value = this.parentNode.parentNode.parentNode.childNodes[1].innerHTML;
            }
        };
    }
</script>