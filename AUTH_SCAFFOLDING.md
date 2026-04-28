# Authentication Scaffolding

## Overview

Complete authentication UI scaffolded from the React design in `src_frontend/routes/auth.tsx`. All forms are static (frontend-only) and ready for backend integration.

## Pages Created

### 1. Login Page (`/login`)
**File**: `resources/js/pages/Login.vue`

**Features**:
- Email and password fields
- "Remember me" checkbox
- "Forgot password?" link
- Google OAuth button (static)
- Link to register page
- Split-screen design with marketing content on the right
- Responsive (marketing panel hidden on mobile)

**Form Fields**:
```typescript
{
    email: string,
    password: string,
    remember: boolean
}
```

---

### 2. Register Page (`/register`)
**File**: `resources/js/pages/Register.vue`

**Features**:
- Name, email, password, and password confirmation fields
- Google OAuth button (static)
- Link to login page
- Same split-screen marketing design
- Responsive layout

**Form Fields**:
```typescript
{
    name: string,
    email: string,
    password: string,
    password_confirmation: string
}
```

---

### 3. Forgot Password Page (`/forgot-password`)
**File**: `resources/js/pages/ForgotPassword.vue`

**Features**:
- Email input field
- Success message after submission (static)
- "Back to sign in" link
- Centered single-column layout
- Shows confirmation message after form submission

**Form Fields**:
```typescript
{
    email: string
}
```

---

### 4. Reset Password Page (`/reset-password/{token}`)
**File**: `resources/js/pages/ResetPassword.vue`

**Features**:
- New password and confirmation fields
- Receives token and email as props from URL
- Centered single-column layout

**Props**:
```typescript
{
    token: string,
    email: string
}
```

**Form Fields**:
```typescript
{
    password: string,
    password_confirmation: string
}
```

---

## Routes Added

**File**: `routes/web.php`

```php
// Auth routes (static forms for now)
Route::get('/login', fn () => Inertia::render('Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Register'))->name('register');
Route::get('/forgot-password', fn () => Inertia::render('ForgotPassword'))->name('password.request');
Route::get('/reset-password/{token}', fn (string $token, Request $request) => Inertia::render('ResetPassword', [
    'token' => $token,
    'email' => $request->query('email', ''),
]))->name('password.reset');
```

---

## Design System

All pages use the existing design system from `resources/css/app.css`:

### Colors
- **Brand**: Indigo accent color
- **Surface**: Card backgrounds
- **Hairline**: Subtle borders
- **Muted foreground**: Secondary text

### Components
- **Input fields**: Rounded with focus states (brand ring)
- **Buttons**: Primary (foreground bg) with hover states
- **Links**: Brand color with underline on hover
- **Marketing panel**: Gradient wash, dot pattern, testimonial card

### Typography
- **Headings**: Inter font, semibold, tight tracking
- **Body**: Inter font, regular weight
- **Labels**: Small, medium weight, uppercase tracking

---

## Marketing Content

The right-side panel includes:

1. **Lumen Pro badge**
2. **Headline**: "The interview prep tool I wish I'd had."
3. **Feature list** with checkmarks:
   - Real voice conversations, not chat
   - Honest, structured feedback in seconds
   - Rewrites in your voice, ready to rehearse
   - Private by default — never used to train models
4. **Testimonial card**:
   - Quote from Maya R.
   - Senior PM, fintech
   - Avatar with initial

---

## Header Updates

**File**: `resources/js/components/SiteHeader.vue`

Updated navigation links:
- "Sign in" → Links to `/login`
- "Start free" → Links to `/register`

---

## Next Steps: Backend Integration

### 1. Install Laravel Breeze or Fortify
```bash
composer require laravel/breeze --dev
php artisan breeze:install api
```

Or use Laravel Fortify for headless auth:
```bash
composer require laravel/fortify
php artisan vendor:publish --provider="Laravel\Fortify\FortifyServiceProvider"
```

### 2. Update Form Submissions

Replace the static `handleSubmit` functions with Inertia form submissions:

**Login.vue**:
```typescript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function handleSubmit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
```

**Register.vue**:
```typescript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function handleSubmit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
```

**ForgotPassword.vue**:
```typescript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

function handleSubmit() {
    form.post('/forgot-password');
}
```

**ResetPassword.vue**:
```typescript
import { useForm } from '@inertiajs/vue3';

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function handleSubmit() {
    form.post('/reset-password', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
```

### 3. Add Validation Error Display

Add error display below each field:

```vue
<input
    v-model="form.email"
    type="email"
    :class="{ 'border-destructive': form.errors.email }"
    class="mt-1.5 w-full rounded-lg border border-hairline..."
/>
<p
    v-if="form.errors.email"
    class="mt-1 text-xs text-destructive"
>
    {{ form.errors.email }}
</p>
```

### 4. Update Routes

Replace static routes with Laravel auth routes:

```php
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
```

### 5. Add Google OAuth (Optional)

Install Laravel Socialite:
```bash
composer require laravel/socialite
```

Add Google credentials to `.env`:
```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://your-app.test/auth/google/callback
```

Update the Google button to link to OAuth route:
```vue
<Link
    href="/auth/google/redirect"
    class="mt-8 flex w-full items-center justify-center gap-2..."
>
    <GoogleIcon />
    Continue with Google
</Link>
```

---

## Testing Checklist

- [x] TypeScript compilation passes
- [x] ESLint passes
- [x] Production build succeeds
- [x] PHP formatting passes
- [ ] Login page renders correctly
- [ ] Register page renders correctly
- [ ] Forgot password page renders correctly
- [ ] Reset password page renders correctly
- [ ] Forms submit (currently console.log only)
- [ ] Navigation links work
- [ ] Responsive design works on mobile
- [ ] Marketing panel shows on desktop, hides on mobile

---

## Design Fidelity

✅ **Matched from React source**:
- Split-screen layout (form left, marketing right)
- Google OAuth button with proper icon
- Email/password divider
- Feature list with checkmarks
- Testimonial card design
- Input field styling with focus states
- Button hover animations
- Typography and spacing
- Color scheme (indigo brand)
- Responsive behavior

---

## File Summary

**Created**:
- `resources/js/pages/Login.vue`
- `resources/js/pages/Register.vue`
- `resources/js/pages/ForgotPassword.vue`
- `resources/js/pages/ResetPassword.vue`

**Modified**:
- `routes/web.php` (added auth routes)
- `resources/js/components/SiteHeader.vue` (updated nav links)

**Ready for**:
- Backend controller integration
- Form validation
- Error handling
- Session management
- OAuth integration
