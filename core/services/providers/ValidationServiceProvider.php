<?php
namespace SaQle\Core\Services\Providers;

use SaQle\Core\Services\Providers\ServiceProvider;
use SaQle\Security\Validation\Validators\{
     MinLengthValidator, MaxLengthValidator, LengthValidator, RegexPatternValidator,
     CharacterSetValidator, BlankValidator, TemporalFormatValidator, FormatValidator,
     TimezoneValidator, MinValidator, MaxValidator, UnsignedValidator, NativeTypeValidator, 
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
         $this->app->rules->add('max_length', MaxLengthValidator::class); //
         $this->app->rules->add('required', RequiredValidator::class); //
         $this->app->rules->add('duration', DurationValidator::class); //
         $this->app->rules->add('max_duration', MaxDurationValidator::class); //
         $this->app->rules->add('min_duration', MinDurationValidator::class); //
         $this->app->rules->add('max_width', MaxWidthValidator::class); //
         $this->app->rules->add('min_width', MinWidthValidator::class); //
         $this->app->rules->add('width', WidthValidator::class); //
         $this->app->rules->add('max_height', MaxHeightValidator::class); //
         $this->app->rules->add('min_height', MinHeightValidator::class); //
         $this->app->rules->add('height', HeightValidator::class); //
         $this->app->rules->add('aspect_ratio', AspectRatioValidator::class); //
         $this->app->rules->add('max_size', MaxSizeValidator::class); //
         $this->app->rules->add('min_size', MinSizeValidator::class); //
         $this->app->rules->add('extensions', ExtensionsValidator::class); //
         $this->app->rules->add('mime_types', MimeTypesValidator::class); //
         $this->app->rules->add('email', EmailValidator::class); //
         $this->app->rules->add('choices', ChoicesValidator::class); //
         $this->app->rules->add('blank', BlankValidator::class); //
         $this->app->rules->add('type', NativeTypeValidator::class); //
         $this->app->rules->add('uuid', UuidValidator::class); //
         $this->app->rules->add('unsigned', UnsignedValidator::class); //
         $this->app->rules->add('min_length', MinLengthValidator::class);//
         $this->app->rules->add('length', LengthValidator::class);//
         $this->app->rules->add('min', MinValidator::class); //
         $this->app->rules->add('max', MaxValidator::class); //
         $this->app->rules->add('min_strength', MinStrengthValidator::class); //
         $this->app->rules->add('pattern', RegexPatternValidator::class); //
         $this->app->rules->add('slug', SlugValidator::class); //
         $this->app->rules->add('json', JsonValidator::class); //
         $this->app->rules->add('ip', IpAddressValidator::class); //
         $this->app->rules->add('url', UrlValidator::class); //
         $this->app->rules->add('phone', PhoneValidator::class); //
         $this->app->rules->add('schemes', SchemesValidator::class); //
         $this->app->rules->add('whitelist', WhitelistValidator::class); //
         $this->app->rules->add('blacklist', BlacklistValidator::class); //
         $this->app->rules->add('precision', PrecisionValidator::class); //
         $this->app->rules->add('scale', ScaleValidator::class); //
         
         $this->app->rules->add('charset', CharacterSetValidator::class);
         $this->app->rules->add('require_tld', RequireTldValidator::class);
         $this->app->rules->add('international', InternationalPhoneValidator::class);
         $this->app->rules->add('countries', PhoneCountryValidator::class);

         $this->app->rules->add('min_time', MinTimeValidator::class);
         $this->app->rules->add('max_time', MaxTimeValidator::class);
         $this->app->rules->add('min_datetime', MinDateTimeValidator::class);
         $this->app->rules->add('max_datetime', MaxDateTimeValidator::class);
         $this->app->rules->add('min_date', MinDateValidator::class);
         $this->app->rules->add('max_date', MaxDateValidator::class);
         $this->app->rules->add('format', FormatValidator::class);
         $this->app->rules->add('timezone', TimezoneValidator::class);
     }
}

