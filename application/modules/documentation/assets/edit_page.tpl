<div class="container">
    <div class="page-header">
        <h1 style="display: inline-block;">{lang('Edit page','documentation')}</h1>
        <div class="langSwitcher pull-right">
            <label class="col-lg-4 control-label m-t_10">{lang('Language','documentation')}:</label>
            <div class="col-lg-8">
                <select name="NewPage[lang]" class="form-control">
                    <option value="3">ru</option>
                    <option value="31">en</option>
                </select>
            </div>
        </div>
    </div>
    {if $errors}
        <div class="alert alert-block alert-danger fade in">
            {echo $errors}
        </div>
    {/if}
    <form method="post" action="/documentation/edit_page/{echo $pageId}">
        <!-- Start. Select with categories names and ids-->
        <h4>{lang('Category','documentation')}:</h4>
        <div class="input-group">
            <select name="NewPage[category]" class="form-control">
                {$this->view("cats_select_edit.tpl", array('tree' => $tree,'sel_cat' => $page['category']));}
            </select>
        </div>
        <!-- End. Select with categories names and ids-->
        <!-- Start. Name input -->
        <h4>{lang('Name','documentation')}:</h4>
        <div class="input-group">
            <input type="text" name="NewPage[title]" value="{echo $page['title']}" class="form-control" placeholder="{lang('Title','documentation')}">
        </div>
        <!-- End. Name input-->
        <!-- Start. Url input-->
        <h4>{lang('Url','documentation')}:</h4>
        <div class="input-group">
            <input type="text" name="NewPage[url]" value="{echo $page['url']}" class="form-control" placeholder="{lang('Url','documentation')}">
        </div>
        <!-- End. Url input -->
        <!-- Start. Textarea with content-->
        <h4>{lang('Content','documentation')}:</h4>
        <textarea class="TinyMCEForm" name="NewPage[prev_text]">
            {echo $page['prev_text']}
        </textarea>
        <!-- End. Textarea with content-->
        <!-- Start. Submit button-->
        <div class="buttonSave">
            <button type="submit" class="btn btn-info pull-right">
                {lang('Save','documentation')}
            </button>
        </div>
        <!-- End. Submit button -->
        {form_csrf()}
    </form>
</div>