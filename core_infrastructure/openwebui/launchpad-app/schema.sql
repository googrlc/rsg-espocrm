-- RSG agency launchpad: categorized links + per-agent favorites
-- Run in the Supabase dashboard SQL editor (rsg-infrastructure project), or via psql.

create table if not exists public.agency_links (
  id          uuid primary key default gen_random_uuid(),
  category    text not null default 'Other',
  name        text not null,
  url         text not null,
  created_by  text,
  created_at  timestamptz not null default now(),
  sort        int not null default 0
);
create index if not exists agency_links_category_idx on public.agency_links(category);

create table if not exists public.agency_link_favorites (
  agent_id     text not null,
  link_id      uuid not null references public.agency_links(id) on delete cascade,
  favorited_at timestamptz not null default now(),
  primary key (agent_id, link_id)
);

alter table public.agency_links          enable row level security;
alter table public.agency_link_favorites enable row level security;

-- Access is gated by private hosting + a passphrase in the app (no Supabase Auth),
-- so RLS is permissive for the anon role. Tighten later if you move to public hosting.
drop policy if exists "anon all links"     on public.agency_links;
drop policy if exists "anon all favorites" on public.agency_link_favorites;
create policy "anon all links"     on public.agency_links          for all to anon, authenticated using (true) with check (true);
create policy "anon all favorites" on public.agency_link_favorites for all to anon, authenticated using (true) with check (true);
