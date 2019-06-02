import PropTypes from "prop-types";

export const user = PropTypes.shape({
    id: PropTypes.number,
    email: PropTypes.string,
    name: PropTypes.string,
    linkedAccountsParsed: PropTypes.shape({
        discord: PropTypes.shape({
            id: PropTypes.number,
            email: PropTypes.string,
            nickname: PropTypes.string,
            avatar: PropTypes.string,
            remote_primary_group: PropTypes.string,
            remote_secondary_groups: PropTypes.string,
            verified: PropTypes.number,
            created_at: PropTypes.string,
            updated_at: PropTypes.string,
        }),
        ips: PropTypes.shape({
            id: PropTypes.number,
            email: PropTypes.string,
            nickname: PropTypes.string,
            avatar: PropTypes.string,
            remote_primary_group: PropTypes.number,
            remote_secondary_groups: PropTypes.string,
            verified: PropTypes.number,
            created_at: PropTypes.string,
            updated_at: PropTypes.string,
        }),
    }),
    created_at: PropTypes.string,
    updated_at: PropTypes.string,
    deleted_at: PropTypes.string,
});

export const skills = PropTypes.arrayOf(
    PropTypes.shape({
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
    })
);

export const sets = PropTypes.arrayOf(
    PropTypes.shape({
        id: PropTypes.number,
        name: PropTypes.string,
    })
);

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

export const dpsParses = PropTypes.arrayOf(
    PropTypes.shape({
        id: PropTypes.number,
        user_id: PropTypes.number,
        character_id: PropTypes.number,
        dps_amount: PropTypes.number,
        sets: PropTypes.array,
        parse_file_hash: PropTypes.shape({
            thumbnail: PropTypes.string,
            large: PropTypes.string
        }),
        superstar_file_hash: PropTypes.shape({
            thumbnail: PropTypes.string,
            large: PropTypes.string
        }),
        discord_notification_message_ids: PropTypes.string,
        processed_by: PropTypes.number,
        reason_for_disapproval: PropTypes.string,
        created_at: PropTypes.string,
        updated_at: PropTypes.string,
        deleted_at: PropTypes.string,
    })
);

export const characters = PropTypes.arrayOf(
    PropTypes.shape({
        id: PropTypes.number,
        user_id: PropTypes.number,
        name: PropTypes.string,
        role: PropTypes.string,
        class: PropTypes.string,
        sets,
        skills,
        content,
        approved_for_midgame: PropTypes.number,
        approved_for_endgame_t0: PropTypes.number,
        approved_for_endgame_t1: PropTypes.number,
        approved_for_endgame_t2: PropTypes.number,
        last_submitted_dps_amount: PropTypes.number,
        dps_parses: dpsParses,
        created_at: PropTypes.string,
        updated_at: PropTypes.string,

    })
);
