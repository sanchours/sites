<?php

/** @var array $aProfiles */
?>

<?php if ($aProfiles): ?>
    <div style="color: #ff0900">
        <div><b><?=Yii::t('gallery', 'change_active_format'); ?></b></div>
        <ul class="b_profiles">
            <?php foreach ($aProfiles as $aProfile): ?>
                <li style="list-style-type: none;">
                    <div><?=Yii::t('gallery', 'profile'); ?> <?=$aProfile['title']; ?></div>
                    <ul>
                        <?php foreach ($aProfile['formats'] as $format): ?>
                            <li>
                                <?=$format['title']; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>