@if(config('captcha.enabled'))
<div class="mt-4 p-4 border rounded shadow">
    <?php $captcha_type = 'flat2'; ?>
    <div class="captcha">
        <div style="height:46px;">{!! captcha_img($captcha_type) !!}</div>
        <button type="button" class="btn btn-danger" class="refresh-captcha" id="refresh-captcha">
            &#x21bb;
        </button>
    </div>
    <x-form-input name="captcha" id="captcha" :label="__('Captcha')" required autofocus />
    <script type="text/javascript">
        let refreshCaptcha = document.querySelector("#refresh-captcha");
        refreshCaptcha.addEventListener('click', function() {
            fetch('refresh-captcha?type={{ $captcha_type }}')
                .then(response => {
                    // console.log(response, response.json());
                    return response.json();
                })
                .then(data => {
                    let captchaElement = document.querySelector('.captcha div');
                    captchaElement.innerHTML = data.captcha;
                    // $(".captcha span").html(data.captcha);
                    // console.log(data)
                });

        });
    </script>
</div>
@endif