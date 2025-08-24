# Drawer Responsive - Solution CSS Override

## Problème Identifié

Le composant `DrawerContent.vue` de Shadcn-vue impose des styles par défaut qui overrident les classes Tailwind :

```vue
<!-- Dans DrawerContent.vue -->
data-[vaul-drawer-direction=right]:w-3/4
data-[vaul-drawer-direction=right]:sm:max-w-sm
```

Ces styles forcent :
- **Mobile** : 75% de largeur (au lieu de 100%)
- **Desktop** : max-width 384px (au lieu de 50%)

## Solution : CSS Override avec `!important`

### Styles CSS Personnalisés

```css
/* Override default drawer styles for responsive behavior */
.drawer-responsive[data-vaul-drawer-direction="right"] {
  /* Mobile: Full width */
  width: 100vw !important;
  max-width: 100vw !important;
}

/* Medium screens and up (≥768px): 2/3 width */
@media (min-width: 768px) {
  .drawer-responsive[data-vaul-drawer-direction="right"] {
    width: 66.67vw !important;
    max-width: 66.67vw !important;
  }
}

/* Large screens and up (≥1024px): 1/2 width */
@media (min-width: 1024px) {
  .drawer-responsive[data-vaul-drawer-direction="right"] {
    width: 50vw !important;
    max-width: 50vw !important;
  }
}

/* Extra large screens and up (≥1280px): 2/5 width */
@media (min-width: 1280px) {
  .drawer-responsive[data-vaul-drawer-direction="right"] {
    width: 40vw !important;
    max-width: 40vw !important;
  }
}

/* XXL screens (≥1536px): Limit maximum width */
@media (min-width: 1536px) {
  .drawer-responsive[data-vaul-drawer-direction="right"] {
    width: 35vw !important;
    max-width: 600px !important;
  }
}
```

## Largeurs par Taille d'Écran (Corrigées)

| Appareil     | Breakpoint | Largeur               | Pourcentage |
| ------------ | ---------- | --------------------- | ----------- |
| 📱 **Mobile** | < 768px    | `100vw`               | 100%        |
| 💻 **Medium** | ≥ 768px    | `66.67vw`             | ~67%        |
| 💻 **Large**  | ≥ 1024px   | `50vw`                | 50%         |
| 🖥️ **XL**     | ≥ 1280px   | `40vw`                | 40%         |
| 🖥️ **XXL**    | ≥ 1536px   | `35vw` ou `600px max` | 35%         |

## Implémentation

### Template
```vue
<DrawerContent class="drawer-responsive h-screen top-0 right-0 left-auto mt-0 rounded-none">
```

### CSS
- Utilise les attributs `data-vaul-drawer-direction` pour cibler spécifiquement les drawers
- `!important` nécessaire pour override les styles inline du composant
- Utilise `vw` (viewport width) pour une vraie responsivité
- Limite maximale sur très grands écrans pour éviter l'étirement

## Résultat Attendu

✅ **Mobile (< 768px)** : Drawer plein écran (100vw)
✅ **Tablette/Medium (≥ 768px)** : Drawer 2/3 écran (66.67vw)
✅ **Desktop (≥ 1024px)** : Drawer moitié écran (50vw)
✅ **Large Desktop (≥ 1280px)** : Drawer 40% écran (40vw)
✅ **Ultra-wide (≥ 1536px)** : Drawer 35% écran ou 600px max
