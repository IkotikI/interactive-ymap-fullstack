<div class="select-wrap">
    <div class="select-button"><span class="name"><?='Выберете город'?></span><div class="icon-arrow-top">
        <?=file_get_contents( plugins_url( '/img/arrow-top.svg', WIDGET_VA_QUIZ . '/widget.php' ) )?>
    </div>
    </div>
    <ul id="" class="cities-select select-options" ?>
        <?php foreach ( $this->cities->terms as $city ): ?>
            <li id="city-<?=$city->slug?>" class="city">
                <input type="radio" id="input-city-<?=$city->slug?>"
                        name="<?=$city->slug?>"
                        value="<?=$city->term_id?>">
                <label for="input-city-<?=$city->slug?>">
                    <?=$city->name?>
                </label>
            </li>
        <?php endforeach;?>
    </ul>
</div>

<style>
    .select-button {
        display: flex;
        gap: 10px;
        user-select: none;
        font-weight: bold;
        color: #007FFF;
    }

    .select-button .icon-arrow-top {
        height: 20px;
        line-height: 2.2rem;
    }

    .select-button .icon-arrow-top svg {

        height: 100%;
        width: auto;

        transition: transform .2s ease-in-out;

    }

    .select-wrap.active .select-button .icon-arrow-top svg {
        transform: rotate(180deg);
    }

    .select-options {
        display: none;
        list-style-type: none;
        position: absolute;
        background-color: rgba(0,0,0,0.6);
        border-radius: 12px;
        padding: 10px;
        margin: 0;
        z-index: 1000;
        top: 35px;
        left: 50%;
        transform: translate(-50%, 0);
    }

    .select-options li {
        padding: 8px 14px;
        color: #fff;
        font-weight: 500;
        width: 220px;
    }

    .select-options input[type="radio"] {
        position: absolute;
        visibility: hidden;

        transition: color .2s ease-in-out;
    }

    .select-options input[type="radio"] + label {
        user-select: none;
    }

    /* .select-options li:hover input[type="radio"] + label,
    .select-options input[type="radio"].active + label {
        color: #007FFF;
    } */

    .select-wrap.active .select-options {
        display: block;
    }
</style>
