export const stubFetchingGroups = cy => {
    cy.fixture('groups.json').as('groups');
    cy.route('GET', '/api/groups', '@groups');
};
