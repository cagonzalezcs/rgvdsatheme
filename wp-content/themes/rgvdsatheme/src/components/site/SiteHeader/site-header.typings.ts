export type MenuItem = {
  id: number;
  title: string;
  url: string;
  target: string;
  external: true;
  classes: string[];
  current: boolean;
  currentParent: boolean;
  currentAncestor: boolean;
  level: number;
  children: MenuItem[];
};

export type SiteHeaderProps = {
  menuItems: MenuItem[];
};
