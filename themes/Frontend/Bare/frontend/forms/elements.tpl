{block name='frontend_forms_elements'}
    <form id="support" name="support" class="{$sSupport.class}" method="post" action="{url controller='ticket' action='index' id=$id}" enctype="multipart/form-data">
    <input type="hidden" name="forceMail" value="{$forceMail|escape}">

        {* Form Content *}
        {block name='frontend_forms_elements_form_content'}
            <div class="forms--inner-form panel--body">
                {foreach $sSupport.sElements as $sKey => $sElement}
                    {if $sSupport.sFields[$sKey]||$sElement.note}
                        {block name='frontend_forms_elements_form_builder'}
                            <div {if $sSupport.sElements[$sKey].typ eq 'textarea'}class="textarea"{elseif $sSupport.sElements[$sKey].typ eq 'checkbox'}class="forms--checkbox"{elseif $sSupport.sElements[$sKey].typ eq 'select'}class="field--select"{/if}>
                                {if $sSupport.sElements[$sKey].typ eq 'select'}<span class="arrow"></span>{/if}

                                {eval var=$sSupport.sFields[$sKey]|replace:'%*%':"{s name='RequiredField' namespace='frontend/register/index'}{/s}"}

                                {if $sSupport.sElements[$sKey].typ eq 'checkbox'}
                                    {$sSupport.sLabels.$sKey|replace:':':''}
                                {/if}

                            </div>
                        {/block}

                        {block name='frontend_forms_elements_form_description'}
                            {if $sElement.note}
                                <p class="forms--description">
                                    {eval var=$sElement.note}
                                </p>
                            {/if}
                        {/block}
                    {/if}
                {/foreach}

                {* Captcha *}
                {block name='frontend_forms_elements_form_captcha'}
                    <div class="forms--captcha">
                        <div class="captcha--placeholder" data-src="{url module=widgets controller=Captcha action=refreshCaptcha}"></div>
                        <strong class="captcha--notice">{s name='SupportLabelCaptcha'}{/s}</strong>
                        <div class="captcha--code">
                            <input type="text" required="required" aria-required="true" name="sCaptcha"{if $sSupport.sErrors.e.sCaptcha} class="has--error"{/if} />
                        </div>
                    </div>
                {/block}

                {* Required fields hint *}
                {block name='frontend_forms_elements_form_required'}
                    <div class="forms--required">{s name='SupportLabelInfoFields'}{/s}</div>
                {/block}

                {* Forms actions *}
                {block name='frontend_forms_elements_form_submit'}
                    <div class="buttons">
                        <button class="btn is--primary is--icon-right" type="submit" name="Submit" value="submit">{s name='SupportActionSubmit'}{/s}<i class="icon--arrow-right"></i></button>
                    </div>
                {/block}
            </div>
        {/block}
    </form>
{/block}

