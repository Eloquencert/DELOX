export type WorkspaceRole = 'owner' | 'admin' | 'member';
export type TaskPriority = 'low' | 'medium' | 'high' | 'urgent';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
}

export interface WorkspaceOption {
    id: number;
    name: string;
    slug: string;
}

export interface WorkspaceProject {
    id: number;
    workspace_id: number;
    name: string;
    key: string;
    description: string | null;
    tasks_count?: number;
}

export interface Workspace {
    id: number;
    name: string;
    slug: string;
    owner_id: number;
    projects: WorkspaceProject[];
    users?: Pick<User, 'id' | 'name'>[];
    users_count?: number;
    pivot?: {
        role: WorkspaceRole;
    };
}

export interface TaskComment {
    id: number;
    task_id: number;
    user_id: number;
    body: string;
    created_at: string;
    user: Pick<User, 'id' | 'name'>;
}

export interface BoardTask {
    id: number;
    project_id: number;
    column_id: number;
    creator_id: number;
    assignee_id: number | null;
    number: number;
    title: string;
    description: string | null;
    priority: TaskPriority;
    position: string;
    due_date: string | null;
    creator: Pick<User, 'id' | 'name'>;
    assignee: Pick<User, 'id' | 'name'> | null;
    comments: TaskComment[];
}

export interface BoardColumn {
    id: number;
    project_id: number;
    name: string;
    position: string;
    tasks: BoardTask[];
}

export interface ProjectBoard {
    id: number;
    name: string;
    key: string;
    workspace_id: number;
}

export interface WorkspaceMember extends User {
    email: string;
    pivot: {
        role: WorkspaceRole;
    };
}
