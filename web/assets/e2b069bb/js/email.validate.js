/*
 * Change email validation
 */
jQuery.extend(jQuery.validator.methods, {
    email: function(a, b) {
        return this.optional(b) || /^([a-z0-9_\.-])+@[a-z0-9-]+\.([a-z]{2,4}\.)?[a-z]{2,4}$/i.test(a);
    }
});