import type { User } from './delox';

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        workspaces: WorkspaceOption[];
    };
    flash: {
        success?: string;
    };
};
