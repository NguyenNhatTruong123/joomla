<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Topic
 *
 * @copyright       Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Config\KunenaConfig;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\Template\KunenaTemplate;
use Kunena\Forum\Libraries\User\KunenaUserHelper;

Text::script('COM_KUNENA_RATE_LOGIN');
Text::script('COM_KUNENA_RATE_NOT_YOURSELF');
Text::script('COM_KUNENA_RATE_ALLREADY');
Text::script('COM_KUNENA_RATE_SUCCESSFULLY_SAVED');
Text::script('COM_KUNENA_RATE_NOT_ALLOWED_WHEN_BANNED');

Text::script('COM_KUNENA_SOCIAL_EMAIL_LABEL');
Text::script('COM_KUNENA_SOCIAL_TWITTER_LABEL');
Text::script('COM_KUNENA_SOCIAL_FACEBOOK_LABEL');
Text::script('COM_KUNENA_SOCIAL_GOOGLEPLUS_LABEL');
Text::script('COM_KUNENA_SOCIAL_LINKEDIN_LABEL');
Text::script('COM_KUNENA_SOCIAL_PINTEREST_LABEL');
Text::script('COM_KUNENA_SOCIAL_WHATSAPP_LABEL');
Text::script('COM_KUNENA_SOCIAL_REDDIT_LABEL');

// Load caret.js always before atwho.js script and use it for autocomplete, emojiis...
$this->addStyleSheet('jquery.atwho.css');
$this->addScript('jquery.caret.js');
$this->addScript('jquery.atwho.js');

$this->addScript('assets/js/topic.js');

$this->ktemplate = KunenaFactory::getTemplate();
$social          = $this->ktemplate->params->get('socialshare');
$quick           = $this->ktemplate->params->get('quick');
$txt             = '';

$this->me = KunenaUserHelper::getMyself();

if ($this->topic->ordering)
{
	$txt .= ' topic-sticky';
}

if ($this->topic->locked)
{
	$txt .= ' topic-locked';
}
?>
<div class="kunena-topic-item <?php echo $txt; ?>">
	<?php if ($this->category->headerdesc) : ?>
        <div class="alert alert-info shadow-lg rounded">
            <a class="close" data-bs-dismiss="alert" href="#">&times;</a>
			<?php echo $this->category->displayField('headerdesc'); ?>
        </div>
	<?php endif; ?>
	
	<div style="display: flex; align-items:baseline; gap: 40px">
		<h1 style="flex: 1;">
			<?php echo KunenaTemplate::getInstance()->getTopicIcon($this->topic); ?>
			<?php
			if ($this->ktemplate->params->get('labels') != 0)
			{
				echo $this->subLayout('Widget/Label')->set('topic', $this->topic)->setLayout('default');
			}
			?>
			<?php echo $this->topic->displayField('subject'); ?>
		</h1>
		<div style="border-style: solid; border-width: thin;color: gainsboro;border-radius: 5px; padding: 6px 10px">

		<div style="display:block; font-size:18px; float:left; line-height:100%; padding-top:7px; color: black"><?php echo $this->me->userid == 0 ? "Average rate" : "Your rate" ?></div>
			<?php if (KunenaConfig::getInstance()->ratingEnabled)
				{
					echo $this->subLayout('Topic/Item/Rating')->set('category', $this->category)
						->set('topic', $this->topic);
				} ?>
		</div>
	</div>

    <div><?php echo $this->subRequest('Topic/Item/Actions')->set('id', $this->topic->id); ?></div>

    <div class="float-start">
		<?php echo $this->subLayout('Widget/Pagination/List')
			->set('pagination', $this->pagination)
			->set('display', true); ?>
    </div>
    <h2 class="float-end">
		<?php echo $this->subLayout('Widget/Search')
			->set('id', $this->topic->id)
			->set('title', Text::_('COM_KUNENA_SEARCH_TOPIC'))
			->setLayout('topic'); ?>
    </h2>

    <div class="clearfix"></div>

	<?php if ($social == 1 && $this->me->socialshare != 0 || $social == 1 && !$this->me->exists()) : ?>
        <div><?php echo $this->subLayout('Widget/Social')->set('me', $this->me)->set('ktemplate', $this->ktemplate); ?></div>
	<?php endif; ?>

	<?php if ($social == 2 && $this->me->socialshare != 0 || $social == 2 && !$this->me->exists()) : ?>
        <div><?php echo $this->subLayout('Widget/Socialcustomtag'); ?></div>
	<?php endif; ?>

	<?php
	if ($this->ktemplate->params->get('displayModule'))
	{
		echo $this->subLayout('Widget/Module')->set('position', 'kunena_topictitle');
	}

	echo $this->subRequest('Topic/Poll')->set('id', $this->topic->id);

	if ($this->ktemplate->params->get('displayModule'))
	{
		echo $this->subLayout('Widget/Module')->set('position', 'kunena_poll');
	}

	$count = 1;
	if ($this->messages)
	{
		echo '<div class="topic-item-messages">';

		foreach ($this->messages as $id => $message)
		{
			echo $this->subRequest('Topic/Item/Message')
				->set('mesid', $message->id)
				->set('location', $id);

			if ($this->ktemplate->params->get('displayModule'))
			{
				echo $this->subLayout('Widget/Module')
					->set('position', 'kunena_msg_row_' . $count++);
			}
		}

		echo '</div>';
	}

	if ($quick == 2 && KunenaConfig::getInstance()->quickReply)
	{
		echo $this->subLayout('Message/Edit')
			->set('message', $this->message)
			->setLayout('full');
	}
	?>

    <div class="float-start">
		<?php echo $this->subLayout('Widget/Pagination/List')
			->set('pagination', $this->pagination)
			->set('display', true); ?>
    </div>
    <div class="float-end">
		<?php echo $this->subLayout('Widget/Search')
			->set('id', $this->topic->id)
			->set('title', Text::_('COM_KUNENA_SEARCH_TOPIC'))
			->setLayout('topic'); ?>
    </div>

    <div><?php echo $this->subRequest('Topic/Item/Actions')->set('id', $this->topic->id); ?></div>

	<?php if ($this->ktemplate->params->get('writeaccess')) : ?>
        <div><?php echo $this->subLayout('Widget/Writeaccess')->set('id', $this->topic->id); ?></div>
	<?php endif; ?>

	<?php
	if ($this->config->enableForumJump)
	{
		echo $this->subLayout('Widget/Forumjump')->set('categorylist', $this->categorylist);
	} ?>
    <div class="clearfix"></div>
    <div class="float-end"><?php echo $this->subLayout('Category/Moderators')->set('moderators', $this->category->getModerators(false)); ?></div>
</div>