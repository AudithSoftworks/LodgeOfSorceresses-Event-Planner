export const stubFetchingUserHeiims = cy => {
    cy.fixture('xhr-data/users/member-with-clearance.json').as('heiims');
    cy.route('GET', '/api/users/6', '@heiims');
};
