<?php
/**
 * Kunena Component
 *
 * @package         Kunena.Template.Aurelia
 * @subpackage      Layout.Rating
 *
 * @copyright       Copyright (C) 2008 - 2022 Kunena Team. All rights reserved.
 * @license         https://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link            https://www.kunena.org
 **/

namespace Kunena\Forum\Site;

\defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;



if ($this->config->ratingEnabled && $this->category->allowRatings)
:
	$this->addStyleSheet('rating.css');
	$this->addScript('rating.js');
	$this->addScript('krating.js');

	Text::script('COM_KUNENA_RATING_SUCCESS_LABEL');
	Text::script('COM_KUNENA_RATING_WARNING_LABEL');

	$star_class = array("fas fa-star s1", "fas fa-star s2", "fas fa-star s3", "fas fa-star s4", "fas fa-star s5");
	$style_class = array("","","","","");
	

	if ($this->topic->rating)
	:
		$rating = floor($this->topic->rating);
		$decimal = $this->topic->rating - $rating;

		for($i = 0; $i < 5; $i++){
			if($i < $rating) {
				$star_class[$i] .= " is-active";
			} else {
				if($decimal != 0) {
					$star_class[$i] .= " decimal";
					$style_class[$i] = "--rating: " . $decimal;
					break;
				}
			}
		}
		?>
		<div id="krating-top"
			 title="<?php echo Text::sprintf('COM_KUNENA_RATE_TOOLTIP', $this->topic->rating, $this->topic->getReviewCount()); ?>"
			 class="hasTooltip">
			<ul class="c-rating">
				<li class="<?php echo $star_class[0]; ?>" data-index="0" style="<?php echo $style_class[0];?>"></li>
				<li class="<?php echo $star_class[1]; ?>" data-index="1" style="<?php echo $style_class[1];?>"></li>
				<li class="<?php echo $star_class[2]; ?>" data-index="2" style="<?php echo $style_class[2];?>"></li>
				<li class="<?php echo $star_class[3]; ?>" data-index="3" style="<?php echo $style_class[3];?>"></li>
				<li class="<?php echo $star_class[4]; ?>" data-index="4" style="<?php echo $style_class[4];?>"></li>			
			</ul>
		</div>
	<?php endif;
endif;
