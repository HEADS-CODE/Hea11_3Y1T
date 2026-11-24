<label class="consent-check auth-terms-check">
    <input type="checkbox" name="accepted" value="1" @checked(old('accepted')) required>
    I agree to Puihaha’s terms and consent to the secure processing of my registration details.
</label>
@error('accepted')<span class="field-error">{{ $message }}</span>@enderror
