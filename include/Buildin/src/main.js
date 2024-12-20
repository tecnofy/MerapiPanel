import "../scss/app.scss";
import "../vendor/fontawesome/css/all.min.css";
import $ from "jquery";
import { toast } from "@il4mb/merapipanel";

window.$ = $;

const Box = {
    "form.needs-validation": (el) => {

        function validateInput(input) {
            const $input = $(input);
            const pattern = $input.attr("pattern");

            if (pattern) {
                const regex = new RegExp(pattern);

                if (!regex.test($input.val())) {
                    // is invalid
                    $input.addClass("is-invalid");
                    $input.removeClass("is-valid");
                } else {
                    // is valid
                    $input.addClass("is-valid");
                    $input.removeClass("is-invalid");
                }

            } else if ($input.attr("minlength") || $input.attr("maxlength")) {
                if ($input.val().length < $input.attr("minlength") || $input.val().length > $input.attr("maxlength")) {
                    // is invalid
                    $input.addClass("is-invalid");
                    $input.removeClass("is-valid");
                } else {
                    // is valid
                    $input.addClass("is-valid");
                    $input.removeClass("is-invalid");
                }

            } else if ($input.attr("min") || $input.attr("max")) {
                if ($input.val() < $input.attr("min") || $input.val() > $input.attr("max")) {
                    // is invalid
                    $input.addClass("is-invalid");
                    $input.removeClass("is-valid");
                } else {
                    // is valid
                    $input.addClass("is-valid");
                    $input.removeClass("is-invalid");
                }
            }

            return $input.hasClass("is-valid");
        }

        function validateForm(form) {

            const $form = $(form);
            const data = {
                el: null,
                valid: true
            }

            for (let i = 0; i < $form.find("input, textarea, select").length; i++) {
                const form_el = $($form.find("input, textarea, select")[i])
                data.el = form_el[0];
                if (!validateInput(form_el[0])) {
                    data.valid = false;
                    break;
                }
            }

            return data;
        }



        el.find("input, textarea, select").on("input", function () {
            validateInput(this);
        });

        el.each(function () {

            this.checkValidity = function () {
                const data = validateForm(this);
                if (!data.valid && data.el) {
                    $(data.el).trigger("focus");
                }
                return data.valid;
            }
            $(this).on("submit", function (e) {

                if (!this.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            })
        });
    }
}

$(() => {


    liveReload();


    $("img").each(function () {

        let $this = $(this);
        if ($this[0].naturalWidth == 0 && $this[0].naturalHeight == 0) {
            $this.attr("error", true);
            let image = new Image();
            image.onload = () => {
                $this.removeAttr("error");
            }
            image.src = $this.attr("src");
        } else {
            $this.css("opacity", 1);
        }
    });
});

function liveReload() {

    const keys = Object.keys(Box);
    for (let i in keys) {
        let el = $(keys[i]);
        let callback = Box[keys[i]];
        if (el.data("init") !== true) {
            callback(el);
            el.data("init", true);
        }
    }

    setTimeout(() => window.requestAnimationFrame(liveReload), 1000);
}