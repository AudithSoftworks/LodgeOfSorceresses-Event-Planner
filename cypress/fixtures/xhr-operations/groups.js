export const groups = cy => {
    cy.fixture('groups.json').as('groups');
    cy.route({
        method: 'GET',
        url: '/api/groups',
        response: '@groups',
    }).as('loadGroups');
};
