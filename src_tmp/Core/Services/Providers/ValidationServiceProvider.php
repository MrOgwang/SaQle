<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Security\Validation\Validators\{
     MinLengthValidator, MaxLengthValidator, LengthValidator, RegexPatternValidator,
     CharacterSetValidator, BlankValidator, TemporalFormatValidator, FormatValidator,
     MinValidator, MaxValidator, UnsignedValidator, NativeTypeValidator, 
     RequiredValidator, DurationValidator, MaxDurationValidator, MinDurationValidator,
     SchemesValidator, RequireTldValidator, MinTimeValidator, MaxTimeValidator, SlugValidator,
     InternationalPhoneValidator, MinStrengthValidator, MaxWidthValidator, MinWidthValidator,
     WidthValidator, MaxHeightValidator, MinHeightValidator, HeightValidator, AspectRatioValidator,
     MaxSizeValidator, MinSizeValidator, ExtensionsValidator, MimeTypesValidator, WhitelistValidator,
     BlacklistValidator, EmailValidator, MinDateTimeValidator, MaxDateTimeValidator, MinDateValidator,
     MaxDateValidator, ChoicesValidator, ScaleValidator, PrecisionValidator, IpAddressValidator,
     JsonValidator, PhoneValidator, PhoneCountryValidator, UuidValidator, UrlValidator
};

class ValidationServiceProvider extends ServiceProvider {
     public function register(): void {

         $this->app->rules->add('required', RequiredValidator::class, 0); //
         $this->app->rules->add('blank', BlankValidator::class, 10); //
         $this->app->rules->add('type', NativeTypeValidator::class, 20); //
         $this->app->rules->add('email', EmailValidator::class, 30); //
         $this->app->rules->add('slug', SlugValidator::class, 30); //
         $this->app->rules->add('json', JsonValidator::class, 30); //
         $this->app->rules->add('ip', IpAddressValidator::class, 30); //
         $this->app->rules->add('url', UrlValidator::class, 30); //
         $this->app->rules->add('phone', PhoneValidator::class, 30); //
         $this->app->rules->add('uuid', UuidValidator::class, 30); //
         $this->app->rules->add('duration', DurationValidator::class, 30); //
         $this->app->rules->add('schemes', SchemesValidator::class, 40); //
         $this->app->rules->add('require_tld', RequireTldValidator::class, 40); //
         $this->app->rules->add('international', InternationalPhoneValidator::class, 40);
         $this->app->rules->add('countries', PhoneCountryValidator::class, 40);
         $this->app->rules->add('charset', CharacterSetValidator::class, 40);
         $this->app->rules->add('pattern', RegexPatternValidator::class, 40); //
         $this->app->rules->add('choices', ChoicesValidator::class, 50); //
         $this->app->rules->add('whitelist', WhitelistValidator::class, 50); //
         $this->app->rules->add('blacklist', BlacklistValidator::class, 50); //
         $this->app->rules->add('length', LengthValidator::class, 60);//
         $this->app->rules->add('max_length', MaxLengthValidator::class, 60); //
         $this->app->rules->add('min_length', MinLengthValidator::class, 60);//
         $this->app->rules->add('min', MinValidator::class, 70); //
         $this->app->rules->add('max', MaxValidator::class, 70); //
         $this->app->rules->add('unsigned', UnsignedValidator::class, 70); //
         $this->app->rules->add('precision', PrecisionValidator::class, 70); //
         $this->app->rules->add('scale', ScaleValidator::class, 70); //
         $this->app->rules->add('min_time', MinTimeValidator::class, 80);
         $this->app->rules->add('max_time', MaxTimeValidator::class, 80);
         $this->app->rules->add('min_datetime', MinDateTimeValidator::class, 80);
         $this->app->rules->add('max_datetime', MaxDateTimeValidator::class, 80);
         $this->app->rules->add('min_date', MinDateValidator::class, 80);
         $this->app->rules->add('max_date', MaxDateValidator::class, 80);
         $this->app->rules->add('max_duration', MaxDurationValidator::class, 80); //
         $this->app->rules->add('min_duration', MinDurationValidator::class, 80); //
         $this->app->rules->add('min_strength', MinStrengthValidator::class, 90); //
         $this->app->rules->add('extensions', ExtensionsValidator::class, 100); //
         $this->app->rules->add('mime_types', MimeTypesValidator::class, 100); //
         $this->app->rules->add('width', WidthValidator::class, 110); //
         $this->app->rules->add('height', HeightValidator::class, 110); //
         $this->app->rules->add('max_width', MaxWidthValidator::class, 120); //
         $this->app->rules->add('min_width', MinWidthValidator::class, 120); //
         $this->app->rules->add('max_height', MaxHeightValidator::class, 120); //
         $this->app->rules->add('min_height', MinHeightValidator::class, 120); //
         $this->app->rules->add('aspect_ratio', AspectRatioValidator::class, 130); //
         $this->app->rules->add('max_size', MaxSizeValidator::class); //
         $this->app->rules->add('min_size', MinSizeValidator::class); //
         $this->app->rules->add('format', FormatValidator::class);
     }
}

