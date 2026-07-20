/**
	 * Render Challenge List Block Callback.
	 */
	public function render_challenge_list_block( $attributes ) {
		if ( ! is_user_logged_in() ) {
			return '<div class="himmah-box" style="padding:15px; background:#fff3cd; border-radius:8px; text-align:center;"><p style="margin:0; color:#856404;">يرجى تسجيل الدخول للوصول إلى قائمة التحديات اليومية.</p></div>';
		}

		$user_id = get_current_user_id();
		$points  = (int) get_user_meta( $user_id, 'himmah_total_points', true );

		$completed_challenges = get_user_meta( $user_id, 'himmah_completed_challenges', true );
		if ( ! is_array( $completed_challenges ) ) {
			$completed_challenges = array();
		}

		// جلب التحديات الفعلية عبر الـ Repository
		$challenges = array();
		if ( class_exists( 'Himmah\Repositories\ChallengeRepository' ) ) {
			$repo       = new ChallengeRepository();
			$challenges = $repo->get_active_challenges( 5 );
		}

		// التحدي الافتراضي في حال عدم وجود تحديات مضافة
		if ( empty( $challenges ) ) {
			$challenges = array(
				array(
					'id'      => 1,
					'title'   => 'إنجاز تحدي هِمّة اليومي',
					'points'  => 10,
					'content' => '',
				),
			);
		}

		ob_start();
		?>
		<div class="himmah-challenge-container" style="background:#f0fdf4; padding:20px; border-radius:12px; border:1px solid #10b981; direction:rtl; font-family:sans-serif;">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
				<h3 style="margin:0; color:#047857;">🎯 التحديات اليومية</h3>
				<span class="himmah-points-badge" style="background:#10b981; color:#fff; padding:6px 14px; border-radius:20px; font-weight:bold; font-size:14px;">
					<?php echo esc_html( $points ); ?> نقطة
				</span>
			</div>
			
			<div class="himmah-challenges-list" style="display:flex; flex-direction:column; gap:10px;">
				<?php foreach ( $challenges as $challenge ) : 
					$is_completed = in_array( (int) $challenge['id'], array_map( 'intval', $completed_challenges ), true );
				?>
					<div class="himmah-challenge-item" style="background:#fff; padding:14px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
						<div>
							<span style="font-weight:600; color:#1f2937; display:block;"><?php echo esc_html( $challenge['title'] ); ?> (+<?php echo esc_html( $challenge['points'] ); ?> نقاط)</span>
							<?php if ( ! empty( $challenge['content'] ) ) : ?>
								<small style="color:#6b7280;"><?php echo esc_html( $challenge['content'] ); ?></small>
							<?php endif; ?>
						</div>
						<?php if ( $is_completed ) : ?>
							<button disabled style="background:#6b7280; color:#fff; border:none; padding:8px 18px; border-radius:6px; font-weight:bold;">
								تم الإنجاز ✅
							</button>
						<?php else : ?>
							<button class="himmah-complete-btn" data-challenge-id="<?php echo esc_attr( $challenge['id'] ); ?>" style="background:#047857; color:#fff; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-weight:bold; transition: background 0.3s;">
								إنجاز التحدي
							</button>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}