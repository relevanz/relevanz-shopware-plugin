{extends file="parent:frontend/index/header.tpl"}

{block name="frontend_index_header_javascript_tracking"}
{$smarty.block.parent}
  {if $Controller == 'index'}
    {$url=$baseURLRT}
    {$url=$url|cat:"action=s&cid="}
    {$url=$url|cat:{$CampaignID}}
  {elseif $Controller == 'listing'}
    {$url=$baseURLRT}
    {$url=$url|cat:"action=c&cid="}
    {$url=$url|cat:{$CampaignID}}
    {$url=$url|cat:"&id="}
    {$url=$url|cat:{$sCategoryCurrent}}
  {elseif $Controller == 'detail'}
    {$url=$baseURLRT}
    {$url=$url|cat:"action=p&cid="}
    {$url=$url|cat:{$CampaignID}}
    {$url=$url|cat:"&id="}
    {$url=$url|cat:{$sArticle.articleID}}
  {elseif $Controller == 'checkout' && {controllerAction} == 'finish'}
      {if $sOrderNumber}
        {foreach $sBasket.content as $product}
          {if $product.articleID != 0}
            {$products[]=$product.articleID}
          {/if}
        {/foreach}
        {$url=$baseURLConv}
        {$url=$url|cat:"cid="}
        {$url=$url|cat:{$CampaignID}}
        {$url=$url|cat:"&orderId="}
        {$url=$url|cat:{$sOrderNumber}}
        {$url=$url|cat:"&amount="}
        {$url=$url|cat:{$sAmount}}
        {$url=$url|cat:"&eventName="}
        {$url=$url|cat:{','|implode:$products}}
        {$url=$url|cat:"&network=relevanz"}
      {/if}
  {/if}

  <script type="text/javascript">
  {literal}var relevanzRetargetingConfig = {
      CampaignID: "{/literal}{$CampaignID}{literal}",
      Controller: "{/literal}{$Controller}{literal}",
      url: "{/literal}{$url}{literal}",
  };{/literal}
  </script>
  {if $url}
    <script type="text/javascript" src="{$url}" async="true"></script>
  {/if}
{/block}
