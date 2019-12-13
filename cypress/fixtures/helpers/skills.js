export const stubFetchingSkills = cy => {
    cy.fixture('.skills.json').as('skills');
    cy.route('GET', '/api/skills', '@skills');
};
