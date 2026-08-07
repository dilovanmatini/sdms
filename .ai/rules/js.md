---
paths:
  - 'resources/js/**'
---

# Js

## Use Flowbite React for UI components
For React UI in resources/js, use Flowbite React components and patterns. Treat https://flowbite-react.com/llms-full.txt as the canonical API/docs source before inventing components. Prefer Flowbite React primitives (Modal, Table, Button, Badge, Card, Alert, Dropdown, Tabs, Sidebar, Navbar, Select, TextInput, Textarea, Checkbox, ToggleSwitch, Pagination, Tooltip, Accordion, etc.) over custom equivalents or other UI kits. Do not introduce new shadcn/Radix UI for new feature work unless extending an existing local wrapper that already depends on it.

## Use Lucide React for icons
Use lucide-react icons only. Pick icon names from https://lucide.dev/icons/. Import named icons from lucide-react (e.g. import { Search } from "lucide-react"). Do not add other icon libraries (Heroicons, Font Awesome, react-icons, etc.) for new work.
