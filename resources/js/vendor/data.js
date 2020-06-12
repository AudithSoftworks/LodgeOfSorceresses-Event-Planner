import PropTypes from "prop-types";

export const content = PropTypes.arrayOf(
    PropTypes.shape({
        id: PropTypes.number,
        name: PropTypes.string,
        short_name: PropTypes.string,
        version: PropTypes.string,
        type: PropTypes.string,
        tier: PropTypes.number,
        created_at: PropTypes.string,
        updated_at: PropTypes.string,
    })
);

export const dpsParse = PropTypes.shape({
    id: PropTypes.number,
    user_id: PropTypes.number,
    character_id: PropTypes.number,
    dps_amount: PropTypes.number,
    sets: PropTypes.array,
    parse_file_hash: PropTypes.shape({
        thumbnail: PropTypes.string,
        large: PropTypes.string,
    }),
    info_file_hash: PropTypes.shape({
        thumbnail: PropTypes.string,
        large: PropTypes.string,
    }),
    discord_notification_message_ids: PropTypes.string,
    processed_by: PropTypes.number,
    reason_for_disapproval: PropTypes.string,
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
    deleted_at: PropTypes.string,
});

export const dpsParses = PropTypes.arrayOf(dpsParse);

export const set = PropTypes.shape({
    id: PropTypes.number,
    name: PropTypes.string,
});

export const sets = PropTypes.arrayOf(set);

export const skill = PropTypes.shape({
    id: PropTypes.number,
    name: PropTypes.string,
    slug: PropTypes.string,
    skill_line: PropTypes.number,
    parent: PropTypes.number,
    type: PropTypes.number,
    effect_1: PropTypes.string,
    effect_2: PropTypes.string,
    cost: PropTypes.string,
    icon: PropTypes.string,
    pts: PropTypes.number,
    cast_time: PropTypes.string,
    target: PropTypes.string,
    range: PropTypes.string,
    unlocks_at: PropTypes.number,
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
});

export const skills = PropTypes.arrayOf(skill);

export const user = PropTypes.shape({
    id: PropTypes.number,
    email: PropTypes.string,
    name: PropTypes.string,
    avatar: PropTypes.string,
    isMember: PropTypes.bool,
    isSoulshriven: PropTypes.bool,
    isAdmin: PropTypes.bool,
    clearanceLevel: PropTypes.shape({
        rank: PropTypes.shape({
            title: PropTypes.string,
        }),
        title: PropTypes.string,
        slug: PropTypes.string,
    }),
    linkedAccountsParsed: PropTypes.shape({
        discord: PropTypes.shape({
            id: PropTypes.number,
            nickname: PropTypes.string,
            avatar: PropTypes.string,
            remote_primary_group: PropTypes.string,
            remote_secondary_groups: PropTypes.array,
            verified: PropTypes.number,
            created_at: PropTypes.string,
            updated_at: PropTypes.string,
        }),
        ips: PropTypes.shape({
            id: PropTypes.number,
            nickname: PropTypes.string,
            avatar: PropTypes.string,
            remote_primary_group: PropTypes.number,
            remote_secondary_groups: PropTypes.array,
            verified: PropTypes.number,
            created_at: PropTypes.string,
            updated_at: PropTypes.string,
        }),
    }),
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
    deleted_at: PropTypes.string,
});

export const users = PropTypes.arrayOf(user);

export const character = PropTypes.shape({
    id: PropTypes.number,
    user_id: PropTypes.number,
    name: PropTypes.string,
    role: PropTypes.string,
    class: PropTypes.string,
    sets,
    skills,
    content,
    approved_for_tier: PropTypes.number,
    last_submitted_dps_amount: PropTypes.number,
    dps_parses_processed: dpsParses,
    dps_parses_pending: dpsParses,
    team_membership: PropTypes.shape({
        team_id: PropTypes.number,
        character_id: PropTypes.number,
        status: PropTypes.number,
        accepted_terms: PropTypes.number,
        created_at: PropTypes.string,
        updated_at: PropTypes.string,
    }),
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
});

export const characters = PropTypes.arrayOf(character);

export const team = PropTypes.shape({
    id: PropTypes.number,
    name: PropTypes.string,
    tier: PropTypes.number,
    discord_role_id: PropTypes.string,
    discord_lobby_channel_id: PropTypes.string,
    discord_rant_channel_id: PropTypes.string,
    members: PropTypes.arrayOf(character),
    led_by: user,
    created_by: user,
});

export const teams = PropTypes.arrayOf(team);

export const attendance = PropTypes.shape({
    id: PropTypes.number,
    text_for_planner: PropTypes.string,
    discord_message_id: PropTypes.string,
    gallery_images: PropTypes.arrayOf(
        PropTypes.shape({
            large: PropTypes.string,
            original: PropTypes.string,
            small: PropTypes.string,
        }),
    ),
    created_by: user,
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
    attendees: PropTypes.arrayOf(user),
});

export const attendances = PropTypes.arrayOf(attendance);
